param(
    [string]$EnvPath = "$PSScriptRoot\.env",
    [string]$OutputFolder = "C:\Backup\FinLedger",
    [int]$KeepDays = 30
)

$ErrorActionPreference = 'Stop'
$logFolder = Join-Path $OutputFolder 'logs'
$logFile = Join-Path $logFolder ("backup_" + (Get-Date -Format 'yyyyMMdd') + ".log")

function Write-Log {
    param([string]$Message)
    $line = "[{0}] {1}" -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Message
    Write-Host $line
    if (Test-Path (Split-Path $logFile -Parent)) {
        Add-Content -Path $logFile -Value $line -Encoding UTF8
    }
}

try {
    if (-not (Test-Path $EnvPath)) {
        throw "File .env tidak ditemukan di $EnvPath"
    }

    if (-not (Test-Path $OutputFolder)) {
        New-Item -ItemType Directory -Path $OutputFolder -Force | Out-Null
    }
    if (-not (Test-Path $logFolder)) {
        New-Item -ItemType Directory -Path $logFolder -Force | Out-Null
    }

    $envLines = Get-Content $EnvPath | Where-Object { $_ -and -not $_.TrimStart().StartsWith('#') }
    $config = @{}
    foreach ($line in $envLines) {
        if ($line -match '^(?<key>[^=]+)=(?<value>.*)$') {
            $key = $matches['key'].Trim()
            $value = $matches['value'].Trim().Trim('"').Trim("'")
            # Unescape Windows path style from .env: C:\\xampp\\...
            $value = $value -replace '\\\\', '\'
            $config[$key] = $value
        }
    }

    $dbHost = if ($config['DB_HOST']) { $config['DB_HOST'] } else { '127.0.0.1' }
    $dbPort = if ($config['DB_PORT']) { $config['DB_PORT'] } else { '3306' }
    $dbName = $config['DB_DATABASE']
    $dbUser = $config['DB_USERNAME']
    $dbPass = $config['DB_PASSWORD']

    if (-not $dbName -or -not $dbUser) {
        throw 'DB_DATABASE dan DB_USERNAME harus diatur di file .env'
    }

    $mysqldumpCandidates = @(
        $config['MYSQLDUMP_PATH'],
        'C:\xampp\mysql\bin\mysqldump.exe',
        'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
        'mysqldump'
    ) | Where-Object { $_ }

    $mysqldump = $null
    foreach ($candidate in $mysqldumpCandidates) {
        if ($candidate -eq 'mysqldump') {
            $cmd = Get-Command mysqldump -ErrorAction SilentlyContinue
            if ($cmd) {
                $mysqldump = $cmd.Source
                break
            }
        } elseif (Test-Path $candidate) {
            $mysqldump = (Resolve-Path $candidate).Path
            break
        }
    }

    if (-not $mysqldump) {
        throw 'mysqldump tidak ditemukan. Set MYSQLDUMP_PATH di .env (contoh: C:\xampp\mysql\bin\mysqldump.exe)'
    }

    $timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
    $dumpPath = Join-Path $OutputFolder ("{0}_backup_{1}.sql" -f $dbName, $timestamp)

    Write-Log "Mulai backup database '$dbName' -> $dumpPath"
    Write-Log "mysqldump: $mysqldump"

    $arguments = @(
        "--host=$dbHost",
        "--port=$dbPort",
        "--user=$dbUser",
        "--password=$dbPass",
        '--single-transaction',
        '--routines',
        '--triggers',
        '--set-charset',
        '--default-character-set=utf8mb4',
        "--result-file=$dumpPath",
        $dbName
    )

    $process = Start-Process -FilePath $mysqldump -ArgumentList $arguments -Wait -PassThru -NoNewWindow
    if ($process.ExitCode -ne 0) {
        throw "Backup gagal. Exit code: $($process.ExitCode)"
    }

    if (-not (Test-Path $dumpPath) -or ((Get-Item $dumpPath).Length -le 0)) {
        throw "File backup kosong atau tidak dibuat: $dumpPath"
    }

    $sizeKb = [math]::Round((Get-Item $dumpPath).Length / 1KB, 2)
    Write-Log "Backup selesai: $dumpPath ($sizeKb KB)"

    # Hapus backup lama (default 30 hari)
    if ($KeepDays -gt 0) {
        $cutoff = (Get-Date).AddDays(-$KeepDays)
        Get-ChildItem -Path $OutputFolder -Filter '*.sql' -File |
            Where-Object { $_.LastWriteTime -lt $cutoff } |
            ForEach-Object {
                Write-Log "Hapus backup lama: $($_.Name)"
                Remove-Item $_.FullName -Force
            }
    }

    exit 0
}
catch {
    $msg = $_.Exception.Message
    try { Write-Log "ERROR: $msg" } catch { Write-Error $msg }
    exit 1
}
