<div class="modal fade" id="coaSearchModal" tabindex="-1" aria-labelledby="coaSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="coaSearchModalLabel"><i class="bi bi-search me-2"></i>Cari Chart of Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="coaModalSearch" class="form-control mb-3" placeholder="Ketik kode atau nama akun..." autocomplete="off">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top"><tr><th>Kode</th><th>Nama akun</th><th>Tipe</th><th></th></tr></thead>
                        <tbody id="coaModalResults"></tbody>
                    </table>
                </div>
                <div id="coaModalEmpty" class="text-center text-muted py-4 d-none">Akun tidak ditemukan.</div>
            </div>
        </div>
    </div>
</div>
