<style>
    .card-expedisi {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 15px;
        padding: 20px;
        border: 1px solid #dee2e6;
    }

    .card-expedisi-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .card-expedisi-header h5 {
        color: #007bff;
        margin: 0;
        font-weight: 600;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .form-control-sm {
        font-size: 0.875rem;
    }

    .table-sm th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
    }

    .btn-action {
        font-size: 0.85rem;
        padding: 6px 12px;
        margin: 2px;
    }

    .status-deteksi {
        background-color: #ffc107;
        color: #856404;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        display: inline-block;
    }

    .driver-section {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 10px;
        background-color: #f8f9fa;
    }

    .driver-header {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .col-md-2, .col-md-3, .col-md-4 {
            margin-bottom: 10px;
        }
    }
    /* sweetalert show */
    .swal2-container {
        z-index: 2000 !important;
    }
    /* Styling Highlight Table */
    table#InvoiceExpTable.table-striped > tbody > tr.selected > * {
        background-color: #cfe2ff !important;
    }

    table#InvoiceExpTable tbody tr:hover {
        cursor: pointer;
    }

    /* Styling Form Input Invoice */
    #InvoiceExpTable tbody tr {
        cursor: pointer;
    }

    #InvoiceExpTable tbody tr.selected {
        background-color: #cfe2ff !important;
    }

    .table-sm td {
        padding: 4px 6px;
    }

    .card-header {
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
    }

    /* Table Gabung Size */
    #GabungExpTableRight,
    #GabungExpTableLeft {
        font-size: 12px;
    }

    #GabungExpTableRight th,
    #GabungExpTableRight td,
    #GabungExpTableLeft th,
    #GabungExpTableLeft td {
        padding: 4px 6px !important;
        vertical-align: middle;
    }

</style>
<div class="container-fluid mt-3">
    <!-- Header Form -->
    <div class="card-expedisi" id="master_form_exp_inv">
        <div class="card-expedisi-header">
            <h5><i class='bx bx-truck me-2'></i>FORM GABUNG EXPEDISI</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label d-block">Filter Gabung</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input"
                            type="radio" name="filter_invoice" id="radio_belum" value="belum" checked>
                        <label class="form-check-label" for="radio_belum">
                            Belum Gabung
                        </label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="filter_invoice" id="radio_sudah" value="sudah">
                        <label class="form-check-label" for="radio_sudah">
                            Sudah Gabung
                        </label>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Tanggal Mulai</label>
                    <input type="date" class="form-control form-control-sm" id="filter_tgl_mulai">
                </div>
                <div class="col-md-3">
                    <label>Tanggal Akhir</label>
                    <input type="date" class="form-control form-control-sm" id="filter_tgl_akhir">
                </div>
                <div class="col-md-3">
                    <label>Filter Data</label>
                    <input type="text" class="form-control form-control-sm" id="filter_invoice_expedisi">
                </div>
                <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button class="btn btn-sm btn-info" id="btn_filter_invoice_expedisi">
                                <i class='bx bx-filter'></i> Filter
                            </button>
                        </div>
                    </div>
            </div>
            <div class="d-flex align-items-start gap-2 text-muted small mb-2" role="note">
                <i class="bx bx-info-circle text-primary fs-5" aria-hidden="true"></i>
                <span>Pilih satu baris sebagai acuan customer, lalu tekan tombol di bawah tabel untuk memilih SJ yang akan digabung. Untuk memperbarui gabungan yang sudah ada, pilih filter <strong>Sudah Gabung</strong> terlebih dahulu.</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="InvoiceExpTable">
                    <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>NO MUAT</th>
                        <th>TGL MUAT</th>
                        <th>CUSTOMER</th>
                        <th>PESANAN AWAL</th>
                        <th>NO GABUNG</th>
                        <th>PESANAN GABUNG</th>
                        <th>RUTE</th>
                        <th>KENDARAAN</th>
                        <th>JUMLAH</th>
                        <th>HARGA</th>
                        <th>DISC</th>
                        <th>DEL CHARGE</th>
                        <th>TOTAL</th>
                        <th>NO SJ</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="col-md-3 col-sm-6 d-flex gap-2">
                <button class="btn btn-primary btn-action flex-fill w-100" id="gabungInvExpBtn">
                    <i class='bx bx-plus-circle me-1'></i>Gabung Surat Jalan
                </button>
            </div>
        </div>
    </div>

    {{-- Bagian Show Detail Invoice --}}
    <div class="d-none" id="form_gabung_inv_exp">
        <div class="row g-3">
            <div class="col-12 d-flex justify-content-between align-items-center gap-2">
                <button type="button" class="btn btn-link text-decoration-none p-0" id="returnInvExpBtn" style="color: #107af3;">
                    <i class='bx bx-chevron-left'></i> Kembali ke Daftar
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tutorialGabungInvoiceExpModal" aria-label="Buka panduan gabung Surat Jalan">
                    <i class="bx bx-help-circle me-1"></i>Panduan Gabung
                </button>
            </div>
            <!-- KIRI -->
            <div class="col-md-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold">
                        DATA MUAT / INVOICE
                    </div>
                    <div class="card-body p-2">
                        <!-- FORM INPUT -->
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small">Nomor Gabung</label>
                                <input type="text" class="form-control form-control-sm" id="no_gabung_exp_inv" readonly>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small">Customer</label>
                                <input type="hidden" id="customer_kode_gabung_exp_inv">
                                <input type="text" class="form-control form-control-sm" id="customer_gabung_exp_inv">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">No. SJ</label>
                                <input type="text" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-8">
                            <label class="form-label small">Item</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="item_gabung_exp_inv" placeholder="Cari item..." readonly>
                                <button class="btn btn-primary" type="button" id="btnItemGabungExp">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </div>

                            <div class="col-md-4">
                                <label class="form-label small">Jumlah (KG)</label>
                                <input type="text" class="form-control form-control-sm text-end" id="jumlah_gabung_exp_inv">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Harga @</label>
                                <input type="text" class="form-control form-control-sm text-end" id="harga_gabung_exp_inv">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Diskon %</label>
                                <input type="text" class="form-control form-control-sm text-end" id="diskon_gabung_exp_inv">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Del charge</label>
                                <input type="text" class="form-control form-control-sm text-end" id="dc_gabung_exp_inv">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">DPP</label>
                                <input type="text" class="form-control form-control-sm text-end" id="dpp_gabung_exp_inv" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Sub Total</label>
                                <input type="text" class="form-control form-control-sm text-end" id="total_gabung_exp_inv" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">PPN %</label>
                                <div class="input-group input-group-sm">
                                    {{-- <span class="input-group-text">11%</span> --}}
                                    <input type="text" class="form-control text-end" id="ppn_gabung_exp_inv">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Grand Total</label>
                                <input type="text" class="form-control form-control-sm text-end" id="grand_total_gabung_exp_inv" readonly>
                            </div>
                            <input type="hidden" id="jenis_hrg_exp_inv">
                        </div>

                        <!-- TABLE KECIL -->
                        <div class="table-responsive mt-3">
                            <table id="GabungExpTableLeft" class="table table-bordered table-striped table-sm w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>NO MUAT</th>
                                        <th>TGL MUAT</th>
                                        <th>NO SJ</th>
                                        <th>PESANAN</th>
                                        <th>RUTE</th>
                                        <th>KENDARAAN</th>
                                        <th>JUMLAH</th>
                                        <th>HARGA</th>
                                        <th>G.TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button class="btn btn-primary" id="gabungInvExpBtnLeft">
                            <i class="bx bx-plus-circle me-1"></i>
                            Gabung Surat Jalan
                        </button>
                    </div>
                </div>
            </div>

            <!-- KANAN -->
            <div class="col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold">
                        DATA SURAT JALAN
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-striped table-sm w-100" id="GabungExpTableRight">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NO MUAT</th>
                                    <th>TGL MUAT</th>
                                    <th>NO SJ</th>
                                    <th>PESANAN</th>
                                    <th>RUTE</th>
                                    <th>KENDARAAN</th>
                                    <th>JUMLAH</th>
                                    <th>HARGA</th>
                                    <th>G.TOTAL</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <button class="btn btn-success w-100" id="addToInvExpBtn">
                        <i class="bx bx-arrow-from-right"></i> Tambah Gabung
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
{{-- Modal Item --}}
<div class="modal fade" id="itemModalInvExp" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div>
                    <h3 id="custNameInvExp"></h3>
                    <h3 id="custKodeInvExp"></h3>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalItemInvExpTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NAMA ITEM</th>
                            <th>DARI</th>
                            <th>SAMPAI</th>
                            <th>RUTE</th>
                            <th>HARGA</th>
                            <th>JENIS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- MODAL --}}
@include('expedisi.expedisi-invoice-modal')
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // ================================= Tabel No Muat =====================================
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#InvoiceExpTable')) {
            $('#InvoiceExpTable').DataTable().destroy();
        }
        // untuk kepentingan ambil nilai CustomerKode saat di highlights
        let selectedRowData = null;
        var tableInvoiceExp = $('#InvoiceExpTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
            url: "{{ route('expedisiInvoice.data') }}",
                data: function(d) {
                    d.tgl_mulai = $('#filter_tgl_mulai').val();
                    d.tgl_akhir = $('#filter_tgl_akhir').val();
                    d.search_muat = $('#filter_invoice_expedisi').val();
                    d.filter_invoice = $('input[name="filter_invoice"]:checked').val();
                }
            },
            // Scroll settings
            scrollX: true,
            scrollY: "400px",
            scrollCollapse: true,
            // Responsive settings
            responsive: true,
            autoWidth: true,
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'NOMUAT' },
                { data: 'TGLMUAT' },
                { data: 'CUSTOMER' },
                { data: 'PESANAN' },
                { data: 'GB' },
                { data: 'PESANANGB' },
                { data: 'rute' },
                { data: 'NAMA_KENDARAAN' },
                { data: 'JUMLAH' },
                { data: 'harga_formatted' },
                { data: 'DISC' },
                { data: 'dc_formatted' },
                { data: 'total_formatted' },
                { data: 'NOSJ' },
                // { data: 'GB' },
            ]
        });

        $('#btn_filter_invoice_expedisi').click(function () {
            tableInvoiceExp.ajax.reload();
        });

        $('input[name="filter_invoice"]').change(function () {
            refreshGabungButton();
            tableInvoiceExp.ajax.reload();
        });
    // ============================= End Of Tabel No Muat =====================================
    // ============================= Highlight No Muat Table =====================================
    $('#InvoiceExpTable tbody').on('click', 'tr', function () {
        // Abaikan child row (kalau responsive aktif)
        if ($(this).hasClass('child')) return;
        if ($(this).hasClass('selected')) {
            // klik ulang → unselect
            $(this).removeClass('selected');
            selectedRowData = null;
        } else {
            // hapus selection lain
            $('#InvoiceExpTable tbody tr.selected').removeClass('selected');
            // select baris ini
            $(this).addClass('selected');
            selectedRowData = tableInvoiceExp.row(this).data();
        }
    });
    // ========================== End Of Highlight No Muat Table ==================================
    // ================================ Pilih No Muat ====================================
    $('#gabungInvExpBtn').on('click', function () {
        // Kosongkan Form Untuk Mencegah Ada Data Sebelumnya Nyangkut
        resetFormGabungInvoice();
        // Set Default Button Jadi Store
        setModeStore();
        if (!selectedRowData) {
            // alert('Pilih data terlebih dahulu');
            Swal.fire({
                title: 'Pilih data !!!',
                text: `Pilih data terlebih dahulu.`,
                icon: 'warning',
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#f39c12', // Warna oranye peringatan
            });
            return;
        }
        // 🚫 SUDAH ADA INVOICE
        // if (selectedRowData.INVOICE && selectedRowData.INVOICE.trim() !== '') {
        //     alert('Invoice sudah terbentuk untuk data ini');
        //     return;
        // }
        let customerKode = selectedRowData.CUSTOMER_KODE;
        let gbExist = selectedRowData.GB;
        let noSJ = selectedRowData.NOSJ;
        console.log('ngah :' + customerKode);
        console.log('nama :' + gbExist);
        console.log('nosurjal :' + noSJ);
        // 🔥 ambil semua NOMUAT customer tsb yg invoice kosong
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            loadDataGabung(customerKode, gbExist);
        });
    });
    // ============================ End Of Pilih No Muat ==================================
    // ============================ End Of Tampilkan Detail Right Table ================================
    function loadDataGabung(customerKode, gbExist, noSJ) {
        // Destroy existing DataTable instance if any
        if ($.fn.DataTable.isDataTable('#GabungExpTableRight')) {
            $('#GabungExpTableRight').DataTable().destroy();
        }

        $('#GabungExpTableRight').DataTable({
            processing: true,
            serverSide: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: "{{ route('expedisiInvoiceGabung.data') }}",
                data: {
                    customer_kode: customerKode,
                    noSJ: noSJ
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false },
                { data: 'NOMUAT' },
                { data: 'TGLMUAT' },
                { data: 'NOSJ' },
                { data: 'PESANAN' },
                { data: 'rute' },
                { data: 'NAMA_KENDARAAN' },
                { data: 'JUMLAH' },
                { data: 'harga_formatted' },
                { data: 'gtotal_formatted' }
            ],
            initComplete: function(settings, json) {
                // Data is ready; populate customer fields if data exists
                if (json.data && json.data.length > 0) {
                    let row = json.data[0];
                    console.log("kode " + row.CUSTOMER_KODE);
                    console.log("kode inv " + gbExist);
                    $('#customer_kode_gabung_exp_inv').val(row.CUSTOMER_KODE);
                    $('#customer_gabung_exp_inv').val(row.CUSTOMER);
                }

                // Check radio invoice mode to determine save or edit
                let filterMode = $('input[name="filter_invoice"]:checked').val();
                if (filterMode === 'sudah') {
                    loadExistingInvoice(gbExist);
                }

                // Hide loading modal and show form
                $('#loading_modal').modal('hide');
                $('#master_form_exp_inv').addClass('d-none');
                $('#form_gabung_inv_exp').removeClass('d-none');
            }
        });
    }
    // ============================ End Of Tampilkan Detail Right Table ==================================
    // =============================== Return Table Expedisi =====================================
    $('#returnInvExpBtn').on('click', function () {
        // reload table
        $('#GabungExpTableLeft').DataTable().clear().draw();
        $('#GabungExpTableRight').DataTable().ajax.reload();
        // reset form
        resetFormGabungInvoice();
        $('#form_gabung_inv_exp').addClass('d-none');
        $('#master_form_exp_inv').removeClass('d-none');
        tableInvoiceExp.ajax.reload();
    });
    // ============================ End Of Return Table Expedisi ==================================
    // ============================ Highlight Tabel Gabung ==================================
    let selectedGabungRows = [];
    $('#GabungExpTableRight tbody').on('click', 'tr', function () {
        let table = $('#GabungExpTableRight').DataTable();
        let data  = table.row(this).data();
        if (!data) return;
        // kalau row ini sudah selected → UNSELECT
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            selectedGabungRows = [];
            return;
        }
        // kalau belum selected → SELECT BARU
        $('#GabungExpTableRight tbody tr').removeClass('selected');
        selectedGabungRows = [];
        $(this).addClass('selected');
        selectedGabungRows.push(data);
    });
    // ============================ End Of Highlight Tabel Gabung ==================================
    // ================================= Select Tabel Gabung ==================================
    let leftTable = $('#GabungExpTableLeft').DataTable({
        processing: true,
        serverSide: false,
        paging: false,
        searching: false,
        info: false,
        columns: [
            {
                data: null,
                orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'NOMUAT' },
            { data: 'TGLMUAT' },
            { data: 'NOSJ' },
            { data: 'PESANAN' },
            { data: 'rute' },
            { data: 'NAMA_KENDARAAN' },
            { data: 'JUMLAH' },
            { data: 'harga_formatted' },
            { data: 'gtotal_formatted' },
            {
                data: null,
                orderable: false,
                width: '40px',
                render: function () {
                    return `
                        <button class="btn btn-sm btn-outline-danger btn-remove-left">
                            <i class="bx bx-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    $('#addToInvExpBtn').on('click', function () {
        if (selectedGabungRows.length === 0) {
            // alert('Pilih data yang akan digabung');
            Swal.fire({
                title: 'Pilih data !!!',
                text: `Pilih data yang akan digabung.`,
                icon: 'warning',
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#f39c12',
            });
            return;
        }

        let leftTable  = $('#GabungExpTableLeft').DataTable();
        let rightTable = $('#GabungExpTableRight').DataTable();

        let leftData = leftTable.rows().data().toArray();

        // 🔍 SET JENIS HRG SEKALI SAJA
        if (leftData.length === 0) {
            $('#jenis_hrg_exp_inv').val(selectedGabungRows[0].JENISHRG);
        }

        selectedGabungRows.forEach(row => {

            // 🔍 CEK APAKAH JENIS PESANAN SUDAH SAMA (BERDASARKAN JENISHRG)
            let jenisRow = getJenisPesanan(row);

            let existingData = leftTable.rows().data().toArray();
            if (existingData.length > 0) {
                let jenisExisting = getJenisPesanan(existingData[0]);

                if (jenisExisting && jenisRow && jenisExisting !== jenisRow) {
                    Swal.fire({
                        title: 'Tidak Bisa Digabung!',
                        text: `Pesanan ${jenisExisting} tidak bisa digabung dengan ${jenisRow}.`,
                        icon: 'error',
                        confirmButtonText: 'Saya Mengerti',
                        confirmButtonColor: '#d33'
                    });
                    return; // skip row ini
                }
            }

            // 🔍 CEK APAKAH NOSJ SUDAH ADA DI TABEL KIRI
            let exists = existingData.some(r => r.NOSJ === row.NOSJ);
            if (exists) {
                Swal.fire({
                    title: 'Data Sudah Ada!',
                    text: `Surat Jalan ${row.NOSJ} sudah terdaftar di sistem.`,
                    icon: 'warning',
                    confirmButtonText: 'Saya Mengerti',
                    confirmButtonColor: '#f39c12',
                });
                return; // skip row ini
            }

            // ➕ tambahkan ke tabel kiri
            leftTable.row.add(row).draw(false);

            // Mendissable button search
            refreshItemButtonState();

            // ❌ hapus dari tabel kanan
            rightTable
                .rows((idx, data) => data.NOSJ === row.NOSJ)
                .remove();

            // 🔍 SET JENISHRG (HANYA SEKALI)
            if (
                $('#jenis_hrg_exp_inv').val() === '' ||
                $('#jenis_hrg_exp_inv').val() === null
            ) {
                $('#jenis_hrg_exp_inv').val(row.JENISHRG);
            }
        });

        rightTable.draw(false);

        // 🔍 SET ITEM & GRAND TOTAL
        // let jenisFinal = $('#jenis_hrg_exp_inv').val();
        // set item dari baris pertama tabel kiri (paling aman)
        let firstRow = $('#GabungExpTableLeft').DataTable().rows().data().toArray()[0];
        // set item dari data pertama
        $('#item_gabung_exp_inv').val(
            decodeHtml(selectedGabungRows[0].PESANAN)
        )
        console.log(selectedGabungRows[0]);
        // $('#jumlah_gabung_exp_inv').val(parseFloat(selectedGabungRows[0].jumlahreal));
        $('#jumlah_gabung_exp_inv').val(parseFloat(selectedGabungRows[0].jumlahreal ??selectedGabungRows[0].JUMLAH ?? 0));
        $('#harga_gabung_exp_inv').val(selectedGabungRows[0].HARGA);
        $('#diskon_gabung_exp_inv').val(parseFloat(selectedGabungRows[0].DISC));
        $('#dc_gabung_exp_inv').val(selectedGabungRows[0].DC);
        $('#total_gabung_exp_inv').val(selectedGabungRows[0].TOTAL);
        $('#dpp_gabung_exp_inv').val(selectedGabungRows[0].TOTAL);
        $('#ppn_gabung_exp_inv').val(parseFloat(selectedGabungRows[0].PPN));
        // if (jenisFinal == 1) {
        // // ECERAN
        // $('#btnItemGabungExp')
        //     .prop('disabled', true)
        //     .addClass('disabled');
        // } else {
        // // BOOKING
        //     $('#btnItemGabungExp')
        //         .prop('disabled', false)
        //         .removeClass('disabled');
        // }

        updateGrandTotalGabung();
        selectedGabungRows = [];
    });
    // ============================ End Of Select Tabel Gabung ==================================
    // ================================ Ubah Nilai Grand Total =====================================
    $('#harga_gabung_exp_inv, #diskon_gabung_exp_inv, #dc_gabung_exp_inv')
    .on('keyup change', updateGrandTotalGabung);
    // ============================ End Of Ubah Nilai Grand Total ==================================
    // ================================= Delete Tabel Gabung ==================================
    $('#GabungExpTableLeft tbody').on('click', '.btn-remove-left', function () {
        let row = leftTable.row($(this).closest('tr'));
        let rowData = row.data();

        // hapus dari tabel kiri
        row.remove().draw(false);

        // Mendissable button search
        refreshItemButtonState();

        // 🔁 (opsional) balikin ke tabel kanan
        let rightTable = $('#GabungExpTableRight').DataTable();
        rightTable.row.add(rowData).draw(false);

        updateGrandTotalGabung();

        if (leftTable.rows().count() === 0) {
           resetFormGabungInvoice({ preserveCustomer: true, preserveInvoice: true });
        }
    });
    // ============================ End Of Delete Tabel Gabung ==================================
    // =================================== Pilih Item =====================================
    $(document).on('click', '#btnItemGabungExp', function(e) {
        var expedisiId = $('#customer_kode_gabung_exp_inv').val();

        if (!expedisiId || expedisiId.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Silahkan Pilih Customer!',
                confirmButtonColor: '#3085d6'
            });
            e.preventDefault();
            return false;
        }

        $('#itemModalInvExp').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalItemInvExpTable')) {
            $('#modalItemInvExpTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalItemInvExpTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('price-customer-modal.price', ':kode') }}".replace(':kode', expedisiId),
                dataSrc: function (json) {
                    // SET INFO CUSTOMER DI ATAS TABEL
                    $("#custNameInvExp").text(json.customer_nama);
                    $("#custKodeInvExp").text(json.customer_kode);
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'KETERANGAN' },
                { data: 'DARI' },
                { data: 'SAMPAI' },
                { data: 'nama_rute', name: 'pc.RUTE' },
                { data: 'harga_html', orderable: false, searchable: false },
                { data: 'jenis_text' },
                { data: 'aksi', orderable: false, searchable: false }
            ]
        });
    });
    // ### Select Button
    $(document).on('click', '.pick-price-exp', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        var pesanan = row.find('td:eq(1)').text();

        // Mengisi nilai ke elemen yang dituju
        $('#item_gabung_exp_inv').val(pesanan);
        // Tutup modal
        $('#itemModalInvExp').modal('hide');
    });
    // =============================== End Of Pilih Item ==================================
    // ================================== Submit Invoice ======================================
    $('#gabungInvExpBtnLeft').on('click', function () {
        // Ambil Nomor Surat Jalan Dari Table kiri Sebagai Parameter
        let nosjList = getNosjFromLeftTable();
        if (nosjList.length === 0) {
            Swal.fire('Warning', 'Tidak ada data yang digabung', 'warning');
            return;
        }
        $('#loading_modal').modal('show');
        let payload = {
            nosj_list: nosjList,

            no_invoice: $('#no_gabung_exp_inv').val(),
            customer_kode: $('#customer_kode_gabung_exp_inv').val(),
            item: $('#item_gabung_exp_inv').val(),

            jumlah: toNumber($('#jumlah_gabung_exp_inv').val()),
            harga: toNumber($('#harga_gabung_exp_inv').val()),
            diskon: toNumber($('#diskon_gabung_exp_inv').val()),
            dc: toNumber($('#dc_gabung_exp_inv').val()),

            dpp: toNumber($('#dpp_gabung_exp_inv').val()),
            total: toNumber($('#total_gabung_exp_inv').val()),
            ppn: toNumber($('#ppn_gabung_exp_inv').val()),
            grand_total: toNumber($('#grand_total_gabung_exp_inv').val()),

            jenis_hrg: $('#jenis_hrg_exp_inv').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $('#loading_modal').one('shown.bs.modal', function () {
            let url = $('#no_gabung_exp_inv').val() ? "{{ route('expedisiInvoice.update') }}" : "{{ route('expedisiInvoice.store') }}";
            var nyobagb = $('#no_gabung_exp_inv').val();
            console.log('nomer gb: ' + nyobagb);
            $.ajax({
                url: url,
                type: "POST",
                data: payload,
                success: function (res) {
                    Swal.fire('Success', res.message, 'success');

                    // reload table
                    $('#GabungExpTableLeft').DataTable().clear().draw();
                    $('#GabungExpTableRight').DataTable().ajax.reload();
                    // reset form
                    resetFormGabungInvoice();

                    // if (res.status) {
                    //     window.open(res.redirect, '_blank');
                    // }
                    // Cetak Invoice Hidden Tab
                    if (res.status) {
                        // printInvoice(res.invoiceNo);
                        $('#loading_modal').modal('hide');
                    }
                },
                error: function (xhr) {
                    $('#loading_modal').modal('hide');
                    let msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
    // =============================== End Of Submit Invoice ==================================
});
    // ########################################################################
    // FUNCTION HELPER:
    // ########################################################################
    // decode HTML entity (ER 99 &gt; 60 -> ER 99 > 60)
    function decodeHtml(str) {
        return $('<textarea/>').html(str).text();
    }

    // fungsi untuk membedakan eceran dan booking (BERDASARKAN JENISHRG)
    function getJenisPesanan(row) {
        if (!row || !row.JENISHRG) return null;

        if (parseInt(row.JENISHRG) === 1) return 'ECERAN';
        if (parseInt(row.JENISHRG) === 2) return 'BOOKING';

        return null;
    }

    // hitung ulang grand total tabel kiri
    function updateGrandTotalGabung() {
        let harga   = toNumber($('#harga_gabung_exp_inv').val());
        let discPct = toNumber($('#diskon_gabung_exp_inv').val());
        let dc      = toNumber($('#dc_gabung_exp_inv').val());

        if (discPct > 100) discPct = 100;
        if (discPct < 0) discPct = 0;

        const PPN_PCT = 11;

        // =====================
        // DISKON NOMINAL
        // =====================
        let diskonNominal = Math.round(harga * (discPct / 100));

        // =====================
        // DPP
        // =====================
        let dpp = Math.round(harga - diskonNominal + dc);
        if (dpp < 0) dpp = 0;

        // =====================
        // PPN NOMINAL
        // =====================
        let ppnNominal = Math.round(dpp * (PPN_PCT / 100));

        // =====================
        // GRAND TOTAL
        // =====================
        let grand = Math.round(dpp + ppnNominal);

        // =====================
        // TAMPILAN
        // =====================
        $('#dpp_gabung_exp_inv').val(formatIDR(dpp));
        $('#total_gabung_exp_inv').val(formatIDR(dpp));
        $('#ppn_gabung_exp_inv').val(PPN_PCT);
        $('#grand_total_gabung_exp_inv').val(formatIDR(grand));

        // =====================
        // NILAI BACKEND
        // =====================
        $('#ppn_value').val(PPN_PCT);
        $('#grand_value').val(grand);
    }

    // fungsi mengecek jumlah tabel da nonaktifkan tombol
    function refreshItemButtonState() {
        let rowCount = $('#GabungExpTableLeft').DataTable().rows().count();

        if (rowCount > 1) {
            // lebih dari satu baris → ENABLE
            $('#btnItemGabungExp')
                .prop('disabled', false)
                .removeClass('disabled');
        } else {
            // satu baris atau kosong → DISABLE
            $('#btnItemGabungExp')
                .prop('disabled', true)
                .addClass('disabled');
        }
    }

    // Mengambil Nilai SJ Dari Tabel Kiri
    function getNosjFromLeftTable() {
        let table = $('#GabungExpTableLeft').DataTable();
        let data  = table.rows().data().toArray();

        return data.map(r => r.NOSJ);
    }

    // Function untuk mode edit mengisi table left
    function loadExistingInvoice(gbExist){
        $.ajax({
            url: "{{ route('expedisiInvoiceGabungExisting.data') }}",
            type: "GET",
            data: {
                gbExist: gbExist
            },
            success: function(res)
            {
                let leftTable = $('#GabungExpTableLeft').DataTable();

                leftTable.clear();

                res.data.forEach(function(row){
                    leftTable.row.add(row);
                });

                leftTable.draw(false);

                // isi form dari master row
                if(res.master){
                    $('#no_gabung_exp_inv').val(gbExist);
                    $('#customer_kode_gabung_exp_inv').val(res.master.CUSTOMER_KODE);
                    $('#customer_gabung_exp_inv').val(res.master.CUSTOMER);
                    $('#item_gabung_exp_inv').val(res.master.PESANAN);
                    $('#jumlah_gabung_exp_inv').val(parseFloat(res.master.JUMLAH));
                    $('#harga_gabung_exp_inv').val(res.master.HARGA);
                    $('#diskon_gabung_exp_inv').val(parseFloat(res.master.DISC));
                    $('#dc_gabung_exp_inv').val(res.master.DC);
                    $('#total_gabung_exp_inv').val(res.master.TOTAL);
                    $('#dpp_gabung_exp_inv').val(res.master.TOTAL);
                    $('#grand_total_gabung_exp_inv').val(res.master.GRAND);
                    $('#ppn_gabung_exp_inv').val(parseFloat(res.master.PPN));
                    $('#jenis_hrg_exp_inv').val(parseFloat(res.master.JENISHRG));
                }
                setModeUpdate();
                updateGrandTotalGabung();
            }
        });
    }

    // Reset Form Tabel Kiri
    function resetFormGabungInvoice({ preserveCustomer = false, preserveInvoice = false } = {}) {
        // Fungsi agar ketika tabel kosong dihapus maka kode customer tidak ikut terhapus
        if (!preserveInvoice) {
            $('#no_gabung_exp_inv').val('');
        }

        if (!preserveCustomer) {
            $('#customer_kode_gabung_exp_inv').val('');
            $('#customer_gabung_exp_inv').val('');
        }

        $('#item_gabung_exp_inv').val('');
        $('#jumlah_gabung_exp_inv').val('');
        $('#harga_gabung_exp_inv').val('');
        $('#diskon_gabung_exp_inv').val('');
        $('#dc_gabung_exp_inv').val('');
        $('#dpp_gabung_exp_inv').val('');
        $('#total_gabung_exp_inv').val('');
        $('#ppn_gabung_exp_inv').val('');
        $('#grand_total_gabung_exp_inv').val('');
        $('#jenis_hrg_exp_inv').val('');
    }

    // Fungsi clean number
    function toNumber(val) {
        if (!val) return 0;
        return parseFloat(
            val.toString()
            .replace(/\./g, '')   // hapus ribuan
            .replace(',', '.')    // jaga-jaga desimal
        ) || 0;
    }

    function formatIDR(num) {
        return num.toLocaleString('id-ID');
    }

    // Ubah Button Update
    function setModeUpdate(){
        let btn = $('#gabungInvExpBtnLeft');

        btn.removeClass('btn btn-primary');
        btn.addClass('btn btn-success');

        btn.html('<i class="bx bx-save me-1"></i> Update Surat Jalan');
    }

    // Ubah Button Store
    function setModeStore(){
        let btn = $('#gabungInvExpBtnLeft');

        btn.removeClass('btn btn-success');
        btn.addClass('btn btn-primary');

        btn.html('<i class="bx bx-plus-circle me-1"></i> Gabung Surat Jalan');
    }

    function refreshGabungButton() {
        let mode = $('input[name="filter_invoice"]:checked').val();
        if (mode === 'sudah') {
            $('#gabungInvExpBtn')
                .removeClass('btn-primary')
                .addClass('btn-success')
                .html('<i class="bx bx-edit me-1"></i>Edit Gabung Surat Jalan');
        } else {
            $('#gabungInvExpBtn')
                .removeClass('btn-success')
                .addClass('btn-primary')
                .html('<i class="bx bx-plus-circle me-1"></i>Gabung Surat Jalan');
        }
    }

    // Fungsi Print Electron JS
    function printInvoice(invoiceNo){

        // ambil printer dari database
        $.get("{{ route('printer.current') }}", function(p){

            var printerName = p.printer;

            if(!printerName){
                alert("Pilih printer dulu");
                return;
            }

            // 🔥 pakai route name (AMAN)
            var url = "{{ route('expedisiInvoice.text', ['invoiceNo' => '__INVOICE__']) }}";
            url = url.replace('__INVOICE__', invoiceNo);

            // ambil TEXT dari Laravel
            $.get(url, function(res){

                var text = res.text;

                // kirim ke Electron
                fetch('http://localhost:3000/print-text', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        printer: printerName,
                        text: text
                    })
                })
                .then(res => res.json())
                .then(res => {
                    console.log("🚀 PRINT TEXT:", res);
                })
                .catch(err => {
                    console.log("❌ ERROR:", err);
                    alert("Print service tidak aktif");
                });

            });

        });
    }
    // function printInvoice(invoiceNo){
    //     $.get("{{ route('printer.current') }}", function(res){
    //         var printerName = res.printer;
    //         console.log("🖨 Printer:", printerName);

    //         if(!printerName){
    //             alert("Pilih printer dulu");
    //             return;
    //         }
    //         var url = "{{ route('expedisiInvoice.pdfInvoice', ['invoiceNo' => '__INVOICE__']) }}";
    //         url = url.replace('__INVOICE__', invoiceNo);
    //         console.log("📄 URL PDF:", url);
    //         // 🔥 cek PDF bisa diakses
    //         fetch(url)
    //         .then(res => {
    //             if(!res.ok) throw new Error("PDF tidak bisa diakses");
    //             console.log("✅ PDF accessible");
    //         })
    //         .catch(err => {
    //             console.log("❌ PDF ERROR:", err);
    //         });
    //         // 🔥 KIRIM KE ELECTRON
    //         fetch('http://localhost:3000/print', {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json'
    //             },
    //             body: JSON.stringify({
    //                 url: url,
    //                 printer: printerName
    //             })
    //         })
    //         .then(res => res.json())
    //         .then(res => {
    //             console.log("🚀 PRINT RESPONSE:", res);
    //         })
    //         .catch(err => {
    //             console.log("❌ PRINT ERROR:", err);
    //             alert("Print service tidak aktif");
    //         });
    //     });
    // }
    // function printInvoice(invoiceNo){
    //     $.get("{{ route('printer.current') }}", function(res){
    //         var printerName = res.printer;
    //         if(!printerName){
    //             alert("Pilih printer dulu");
    //             return;
    //         }
    //         var url = "{{ route('expedisiInvoice.pdfInvoice', ['invoiceNo' => '__INVOICE__']) }}";
    //         url = url.replace('__INVOICE__', invoiceNo);
    //         if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Open){
    //             var cpj = new JSPM.ClientPrintJob();
    //             cpj.clientPrinter = new JSPM.InstalledPrinter(printerName);
    //             var file = new JSPM.PrintFileURL(
    //                 url,
    //                 JSPM.FileSourceType.URL,
    //                 "invoice-" + invoiceNo + ".pdf",
    //                 1
    //             );
    //             cpj.files.push(file);
    //             cpj.sendToClient();

    //         }else{
    //             alert("JSPrintManager belum aktif");
    //         }
    //     });
    // }
</script>
