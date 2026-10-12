$ErrorActionPreference = 'Stop'

$projectDir = Split-Path -Parent $PSScriptRoot
$archive = Join-Path $env:TEMP 'finledger-deploy.tar.gz'
$remoteScript = Join-Path $PSScriptRoot 'deploy-hosting-remote.sh'
$askPassCmd = (Resolve-Path (Join-Path $PSScriptRoot 'deploy-ssh-askpass.cmd')).Path

$DeploySshHost = '202.10.40.136'
$DeploySshUser = 'root'
$DeploySshPort = 22
$server = "$DeploySshUser@$DeploySshHost"

function Test-TcpPortOpen {
    param(
        [string]$HostName,
        [int]$Port = 22,
        [int]$TimeoutMs = 5000
    )

    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $async = $client.BeginConnect($HostName, $Port, $null, $null)
        $waited = $async.AsyncWaitHandle.WaitOne($TimeoutMs, $false)
        if (-not $waited) {
            $client.Close()
            return $false
        }
        $client.EndConnect($async)
        $ok = $client.Connected
        $client.Close()
        return $ok
    } catch {
        return $false
    }
}

function ConvertFrom-SecureStringPlain {
    param([System.Security.SecureString]$Secure)
    $bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($Secure)
    try {
        return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)
    } finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)
    }
}

function Invoke-SshWithPassword {
    param(
        [Parameter(Mandatory)][string[]]$ArgumentList,
        [Parameter(Mandatory)][string]$Password
    )

    $env:DEPLOY_SSH_PASSWORD = $Password
    $env:SSH_ASKPASS = $askPassCmd
    $env:SSH_ASKPASS_REQUIRE = 'force'
    $env:DISPLAY = 'needed'
    $prev = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        # Paksa autentikasi password (jangan diam-diam pakai SSH key)
        $extra = @(
            '-o', 'PreferredAuthentications=password',
            '-o', 'PubkeyAuthentication=no',
            '-o', 'StrictHostKeyChecking=accept-new',
            '-o', 'ConnectTimeout=20'
        )
        & ssh.exe (@($extra) + $ArgumentList)
        return $LASTEXITCODE
    }
    finally {
        $ErrorActionPreference = $prev
        Remove-Item Env:DEPLOY_SSH_PASSWORD -ErrorAction SilentlyContinue
        Remove-Item Env:SSH_ASKPASS -ErrorAction SilentlyContinue
        Remove-Item Env:SSH_ASKPASS_REQUIRE -ErrorAction SilentlyContinue
        Remove-Item Env:DISPLAY -ErrorAction SilentlyContinue
    }
}

function Invoke-ScpWithPassword {
    param(
        [Parameter(Mandatory)][string[]]$ArgumentList,
        [Parameter(Mandatory)][string]$Password
    )

    $env:DEPLOY_SSH_PASSWORD = $Password
    $env:SSH_ASKPASS = $askPassCmd
    $env:SSH_ASKPASS_REQUIRE = 'force'
    $env:DISPLAY = 'needed'
    $prev = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        $extra = @(
            '-O',
            '-o', 'PreferredAuthentications=password',
            '-o', 'PubkeyAuthentication=no',
            '-o', 'StrictHostKeyChecking=accept-new',
            '-o', 'ConnectTimeout=20'
        )
        & scp.exe (@($extra) + $ArgumentList)
        return $LASTEXITCODE
    }
    finally {
        $ErrorActionPreference = $prev
        Remove-Item Env:DEPLOY_SSH_PASSWORD -ErrorAction SilentlyContinue
        Remove-Item Env:SSH_ASKPASS -ErrorAction SilentlyContinue
        Remove-Item Env:SSH_ASKPASS_REQUIRE -ErrorAction SilentlyContinue
        Remove-Item Env:DISPLAY -ErrorAction SilentlyContinue
    }
}

if (-not (Test-Path -LiteralPath (Join-Path $projectDir 'artisan'))) {
    throw "artisan tidak ditemukan di $projectDir"
}

if (-not (Test-Path -LiteralPath $remoteScript)) {
    throw "deploy-hosting-remote.sh tidak ditemukan."
}

Write-Host '[0/4] Memeriksa sintaks AppServiceProvider...'
& php.exe -l (Join-Path $projectDir 'app\Providers\AppServiceProvider.php')
if ($LASTEXITCODE -ne 0) {
    throw 'Sintaks AppServiceProvider.php tidak valid; deployment dibatalkan.'
}

Write-Host "[0b/4] Cek koneksi TCP $DeploySshHost port $DeploySshPort ..."
if (-not (Test-TcpPortOpen -HostName $DeploySshHost -Port $DeploySshPort -TimeoutMs 8000)) {
    throw @"
Tidak bisa connect ke ${DeploySshHost}:$DeploySshPort (Connection timed out).
Ini BUKAN masalah password.

Cek:
1) Internet / VPN ke server hosting
2) Firewall PC / router memblokir port 22
3) VPS hidup dan SSH (port 22) terbuka
4) Coba di CMD: ssh $server
"@
}

Write-Host ''
Write-Host "Login SSH: $server" -ForegroundColor Cyan
Write-Host 'Masukkan password root SSH (tidak disimpan ke file):' -ForegroundColor Yellow
$securePass = Read-Host -AsSecureString 'Password'
$DeploySshPassword = ConvertFrom-SecureStringPlain -Secure $securePass
if ([string]::IsNullOrWhiteSpace($DeploySshPassword)) {
    throw 'Password kosong. Deployment dibatalkan.'
}
Write-Host 'Auth SSH: password yang baru diketik (sekali jalan ini saja)'
Write-Host ''

if (Test-Path -LiteralPath $archive) {
    Remove-Item -LiteralPath $archive -Force
}

Write-Host '[1/4] Membuat arsip deployment...'
Push-Location $projectDir
try {
    & tar.exe -czf $archive `
        --exclude='./.env' `
        --exclude='./.git' `
        --exclude='./.agents' `
        --exclude='./.codex' `
        --exclude='./node_modules' `
        --exclude='./vendor' `
        --exclude='./storage/logs' `
        --exclude='./storage/framework' `
        --exclude='./storage/app' `
        --exclude='./bootstrap/cache' `
        --exclude='./database/database.sqlite' `
        --exclude='./.phpunit.cache' `
        --exclude='./temp_*' `
        --exclude='./tmp_*' `
        --exclude='./test-result.txt' `
        --exclude='./scripts' `
        .

    if ($LASTEXITCODE -ne 0) {
        throw 'Gagal membuat arsip deployment.'
    }
}
finally {
    Pop-Location
}

Write-Host '[2/4] Mengunggah arsip dan script ke VPS...'
# -O sudah ditambahkan di dalam Invoke-ScpWithPassword
$scpArgs = @($archive, $remoteScript, "${server}:/tmp/")
$scpExit = Invoke-ScpWithPassword -ArgumentList $scpArgs -Password $DeploySshPassword
if ($scpExit -ne 0) { throw "Upload arsip dan script gagal. Exit=$scpExit" }

Write-Host '[3/4] Menjalankan deployment di VPS...'
# Pastikan script remote berakhiran LF (hindari error aneh dari CRLF Windows)
$fixRemote = @'
sed -i "s/\r$//" /tmp/deploy-hosting-remote.sh 2>/dev/null || true
bash /tmp/deploy-hosting-remote.sh
echo "__REMOTE_EXIT__:$?"
'@

$sshArgs = @($server, $fixRemote)
$env:DEPLOY_SSH_PASSWORD = $DeploySshPassword
$env:SSH_ASKPASS = $askPassCmd
$env:SSH_ASKPASS_REQUIRE = 'force'
$env:DISPLAY = 'needed'
$prev = $ErrorActionPreference
$ErrorActionPreference = 'Continue'
$remoteLines = @()
try {
    $sshCmdArgs = @(
        '-o', 'PreferredAuthentications=password',
        '-o', 'PubkeyAuthentication=no',
        '-o', 'StrictHostKeyChecking=accept-new',
        '-o', 'ConnectTimeout=20'
    ) + $sshArgs
    $remoteLines = & ssh.exe @sshCmdArgs 2>&1 | ForEach-Object { "$_" }
} finally {
    $ErrorActionPreference = $prev
    Remove-Item Env:DEPLOY_SSH_PASSWORD -ErrorAction SilentlyContinue
    Remove-Item Env:SSH_ASKPASS -ErrorAction SilentlyContinue
    Remove-Item Env:SSH_ASKPASS_REQUIRE -ErrorAction SilentlyContinue
    Remove-Item Env:DISPLAY -ErrorAction SilentlyContinue
}

foreach ($line in $remoteLines) {
    Write-Host $line
}

$sshExit = 1
$exitLine = ($remoteLines | Where-Object { $_ -match '^__REMOTE_EXIT__:(\d+)$' } | Select-Object -Last 1)
if ($exitLine -match '^__REMOTE_EXIT__:(\d+)$') {
    $sshExit = [int]$Matches[1]
} elseif ($null -ne $LASTEXITCODE) {
    $sshExit = [int]$LASTEXITCODE
}

# Hapus password dari memori script
$DeploySshPassword = $null
$securePass = $null

if ($sshExit -ne 0) {
    $tail = ($remoteLines | Select-Object -Last 30) -join [Environment]::NewLine
    throw @"
Proses deployment di VPS gagal (exit=$sshExit).
Cuplikan output terakhir:
$tail
"@
}

Write-Host '[4/4] Membersihkan arsip lokal...'
Remove-Item -LiteralPath $archive -Force

Write-Host 'OK: deployment selesai dan health check HTTP 200.'
