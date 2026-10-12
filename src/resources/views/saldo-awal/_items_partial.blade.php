<div class="card border-0 shadow-none mb-3 locked-items-panel">
    <div class="card-header bg-transparent px-0 d-flex justify-content-between align-items-center border-0 mb-2">
        <div>
            <h5 class="mb-0 fw-bold text-secondary text-uppercase fs-6 tracking-wider">Item Jurnal</h5>
        </div>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="tambahItem()">
            <i class="bi bi-plus-lg"></i> Tambah Baris
        </button>
    </div>

    <div class="item-table-wrapper" style="max-height:60vh;">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 35%">Akun Posting</th>
                        <th style="width: 35%">Keterangan Baris</th>
                        <th class="text-end" style="width: 13%">Debit</th>
                        <th class="text-end" style="width: 13%">Kredit</th>
                        <th class="text-center" style="width: 4%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="saldoAwalBody">
                    @php
                    $oldRows = old('coa_id');
                    $items = $oldRows !== null ? $oldRows : ($latestDraft?->items?->pluck('coa_id')->toArray() ?? []);
                    $descriptions = old('deskripsi_item', $latestDraft?->items?->pluck('deskripsi')->toArray() ?? []);
                    $debits = old('debit', $latestDraft?->items?->pluck('debit')->toArray() ?? []);
                    $kredits = old('kredit', $latestDraft?->items?->pluck('kredit')->toArray() ?? []);
                    @endphp

                    @if(count($items) > 0)
                    @foreach($items as $index => $coaId)
                    @php $selectedAccount = $coas->firstWhere('id', $coaId); @endphp
                    <tr>
                        <td class="account-cell ps-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control form-control-sm account-search border-start-0" placeholder="Ketik kode/nama akun..." value="{{ optional($selectedAccount)->kode_akun ? optional($selectedAccount)->kode_akun . ' - ' . optional($selectedAccount)->nama_akun : '' }}">
                            </div>
                            <select name="coa_id[]" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach($coas as $coa)
                                <option value="{{ $coa->id }}" {{ $coaId == $coa->id ? 'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" value="{{ old('deskripsi_item.' . $index, $descriptions[$index] ?? '') }}" placeholder="Keterangan opsional">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="debit[]" class="form-control form-control-sm text-end debit" value="{{ old('debit.' . $index, $debits[$index] ?? 0) }}">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="kredit[]" class="form-control form-control-sm text-end kredit" value="{{ old('kredit.' . $index, $kredits[$index] ?? 0) }}">
                        </td>
                        <td class="text-center pe-3">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row" aria-label="Hapus">
                                <i class="bi bi-trash3 fs-6"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    @for($i = 0; $i < ($initialRows ?? 2); $i++)
                        <tr>
                        <td class="account-cell ps-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control form-control-sm account-search border-start-0" placeholder="Ketik kode/nama akun...">
                            </div>
                            <select name="coa_id[]" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach($coas as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" placeholder="Keterangan opsional">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="debit[]" class="form-control form-control-sm text-end debit" value="0">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="kredit[]" class="form-control form-control-sm text-end kredit" value="0">
                        </td>
                        <td class="text-center pe-3">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row" aria-label="Hapus">
                                <i class="bi bi-trash3 fs-6"></i>
                            </button>
                        </td>
                        </tr>
                        @endfor
                        @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
