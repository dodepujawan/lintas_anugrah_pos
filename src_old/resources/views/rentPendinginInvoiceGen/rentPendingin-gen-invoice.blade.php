{{-- Bagian Tabel Dingin Kwitansi --}}
<div class="container mt-3" id="table_kwt_dgn">
    <div class="card p-3">
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tutorialRentPendinginInvoiceModal" aria-label="Buka panduan proses invoice Rent Pendingin">
                <i class="bx bx-help-circle me-1"></i>Panduan Invoice
            </button>
        </div>
        <h5 class="text-center mb-3">FORM PROSES INVOICE MOBIL PENDINGIN</h5>
        <div class="mb-3">
            <form id="form-export-excel" action="{{ route('laporan.rentPendinginGenerate.export') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">
                            Status Invoice
                        </label>
                        <label>
                            <input type="radio" name="filter_kwt_dgn" value="belum" checked>
                            Belum Invoice
                        </label>
                        <label class="ms-3">
                            <input type="radio" name="filter_kwt_dgn" value="sudah">
                            Sudah Invoice
                        </label>
                    </div>
                    <label class="form-label">
                        Export Laporan Excel
                    </label>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Tanggal Dari
                        </label>
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ date('Y-m-01') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Tanggal Sampai
                        </label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_invoice_ren" id="customer_invoice_ren" class="form-control">
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success">
                            Export Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table id="DgnKwtTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Invoice</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Grand Total</th>
                        <th>No Surat Jalan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
{{-- Bagian Form Dingin Kwitansi --}}
<div class="container-fluid mt-2 d-none" id="form_kwt_dgn">
    <div class="card shadow-sm border-0">
        <!-- HEADER -->
        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color:#b7e1b0;">
            <button class="btn btn-sm btn-link text-decoration-none p-0" id="returnInvDgnGenBtn" style="color: #107af3;">
                <i class='bx bx-chevron-left'></i>
                Kembali
            </button>
            <h5 class="mb-0 fw-bold">
                FORM PROSES INVOICE MOBIL PENDINGIN
            </h5>
            <div style="width:80px"></div>
        </div>
        <div class="card-body p-3">
            <input type="hidden" id="kwt_exp_flag_dgn">
            <!-- ALERT -->
            <div id="edit_mode_alert_dgn" class="alert alert-warning text-center fw-bold py-2 mb-3" style="display:none;">
                ⚠ MODE EDIT INVOICE MOBIL PENDINGIN
            </div>

            <!-- TOP SECTION -->
            <div class="row g-3">
                <!-- LEFT -->
                <div class="col-lg-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="small fw-bold">
                                        TGL INVOICE
                                    </label>

                                    <input type="date" id="tgl_invoice_kwt_dgn" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">
                                        SUB TOTAL
                                    </label>
                                    <input type="text" id="sub_total_kwt_dgn" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">
                                        D.CHARGE
                                    </label>

                                    <input type="text" id="d_charge_kwt_dgn" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">
                                        TOTAL
                                    </label>

                                    <input type="text" id="total_kwt_dgn" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-3">
                                    <label class="small fw-bold">
                                        DISC %
                                    </label>

                                    <input type="text" id="disc_persen_kwt_dgn" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-3">
                                    <label class="small fw-bold">
                                        DISC Rp
                                    </label>
                                    <input type="text" id="disc_rp_kwt_dgn" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">
                                        DPP
                                    </label>

                                    <input type="text" id="dpp_kwt_dgn" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">
                                        PPN %
                                    </label>

                                    <input type="text" id="ppn_persen_kwt_dgn" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">
                                        GRAND TOTAL
                                    </label>
                                    <input type="text" id="grand_kwt_dgn" class="form-control form-control-sm text-end fw-bold" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="small fw-bold">
                                        NO FAKTUR
                                    </label>
                                    <input type="text" id="no_faktur_kwt_dgn" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">
                                        NOMOR MUAT
                                    </label>
                                    <input type="text" id="nomor_muat_kwt_dgn" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">
                                        NOMOR SJ
                                    </label>
                                    <input type="text" id="nomor_sj_kwt_dgn" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">
                                        KENDARAAN
                                    </label>
                                    <input type="text" id="kendaraan_kwt_dgn" class="form-control form-control-sm" readonly>
                                </div>

                                <div class="col-12">
                                    <label class="small fw-bold">
                                        CUSTOMER
                                    </label>
                                    <input type="text" id="customer_kwt_dgn" class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- PAYMENT -->
            <div class="card border-0 bg-secondary-subtle mt-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-3">
                            <label class="small fw-bold">
                                BAYAR
                            </label>
                            <input type="text" id="bayar_kwt_dgn" class="form-control form-control-sm text-end">
                        </div>
                        <div class="col-lg-2">
                            <label class="small fw-bold">
                                TOP
                            </label>
                            <input type="number" id="top_kwt_dgn" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-3">
                            <label class="small fw-bold">
                                TGL JTP
                            </label>
                            <input type="date" id="tgl_jtp_kwt_dgn" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-4">
                            <label class="small fw-bold">
                                PIUTANG
                            </label>
                            <input type="text" id="piutang_kwt_dgn" class="form-control form-control-sm text-end fw-bold" readonly>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-sm btn-primary" id="proses_kwt_dgn">PROSES
                        </button>
                        <button class="btn btn-sm btn-success" id="print_kwt_dgn">
                            Print
                        </button>
                        <button class="btn btn-sm btn-info" id="pdf_kwt_dgn">
                            Cetak Pdf
                        </button>
                        <button class="btn btn-sm btn-secondary" id="kembali_kwt_dgn">KEMBALI</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('rentPendinginInvoiceGen.rentPendingin-gen-invoice-modal')
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// ================================= Tabel Rent Edit Dingin =====================================
    if ($.fn.DataTable.isDataTable('#DgnKwtTable')) {
        $('#DgnKwtTable').DataTable().destroy();
    }
    let table_kwt_dgn = $('#DgnKwtTable').DataTable({
        processing: true,
        serverSide: true,
        ajax:{
            url: "{{ route('rentPendinginInvGen.data') }}",
            data: function (d) {
                d.status_inv_gen = $('input[name="filter_kwt_dgn"]:checked').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'INVOICE', name: 'INVOICE' },
            { data: 'TGLINVOICE', name: 'TGLINVOICE' },
            { data: 'CUSTOMER', name: 'CUSTOMER' },
            { data: 'GRAND', name: 'GRAND', className: 'text-end' },
            // { data: 'PIUTANG', name: 'PIUTANG', className: 'text-end' },
            { data: 'NOSJ', name: 'NOSJ', visible: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Mengubah Radio Status Kwitansi
    $('input[name="filter_kwt_dgn"]').on('change', function() {

        let status = $(this).val();

        if(status === 'sudah'){
            table_kwt_dgn.column(5).visible(true); // tampilkan kolom No Kwitansi
        } else {
            table_kwt_dgn.column(5).visible(false);
        }

        table_kwt_dgn.ajax.reload();
    });
// ============================== End Of Tabel Rent Edit Dingin ==================================
// =============================== Select Customer Expedisi ===================================
    $('#customer_invoice_ren').select2({
        theme: "bootstrap-5",
        width: '100%',
        placeholder: 'Pilih Customer',
        allowClear: true,
        ajax: {
            url: '{{ route("customer_select") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            }
        }
    });
// =============================== End Of Select Customer Expedisi ===================================
// =============================== Form Detail Kwitansi Dingin ===================================
    $(document).on('click', '.btn-show-invoice-dgn-gen', function() {

        let nosj = $(this).data('nosj');

        // bisa ajax ambil detail invoice juga kalau mau
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('rentPendinginInvGen.show', ':kode') }}".replace(':kode', nosj),
                type: "GET",
                success: function(response) {

                    if (!response.status) {
                        alert(response.message);
                        return;
                    }
                    $('#loading_modal').modal('hide');
                    $('#table_kwt_dgn').addClass('d-none');
                    $('#form_kwt_dgn').removeClass('d-none');
                    // Clear Form Dulu
                    clearAllKwtDgn();

                    let d = response.data;
                    // ===== LEFT SIDE =====
                    $('#sub_total_kwt_dgn').val(formatRupiah(d.sub_total));
                    $('#d_charge_kwt_dgn').val(formatRupiah(d.d_charge));
                    $('#total_kwt_dgn').val(formatRupiah(d.total));

                    $('#disc_persen_kwt_dgn').val(d.disc_persen);
                    $('#disc_rp_kwt_dgn').val(formatRupiah(d.disc_rp));

                    $('#dpp_kwt_dgn').val(formatRupiah(d.total - d.disc_rp));
                    $('#ppn_persen_kwt_dgn').val(formatRupiah(d.ppn));
                    $('#grand_kwt_dgn').val(formatRupiah(d.grand));

                    // $('#tgl_invoice_kwt_dgn').val(
                    //     d.tgl_invoice.substring(0,10)
                    // );

                    // ===== RIGHT SIDE =====
                    $('#no_faktur_kwt_dgn').val(d.invoice);
                    $('#nomor_muat_kwt_dgn').val(d.nomor_muat);
                    $('#nomor_sj_kwt_dgn').val(d.nomor_sj);
                    $('#kendaraan_kwt_dgn').val(d.kendaraan);
                    $('#customer_kwt_dgn').val(d.customer);

                    // ===== BAYAR SECTION =====
                    $('#kwt_exp_flag_dgn').val(0);
                    $('#bayar_kwt_dgn').val(0);
                    $('#top_kwt_dgn').val(0);
                    $('#tgl_jtp_kwt_dgn').val(d.tgl_jt ? d.tgl_jt.substring(0,10) : '');
                    $('#piutang_kwt_dgn').val(formatRupiah(d.grand));

                    $('#print_kwt_dgn, #pdf_kwt_dgn').hide();
                },
                error: function(xhr) {
                    $('#loading_modal').modal('hide');
                    alert('Terjadi kesalahan server');
                }
            });
        });
    });

// ============================ End Of Form Detail Kwitansi Dingin ===============================
// ============================ Jumlah Bayar Invoice Dingin ===============================
    $(document).on('keyup change', '#bayar_kwt_dgn', function () {
        let bayar = unformatRupiah(
            $('#bayar_kwt_dgn').val()
        );
        let grand = unformatRupiah(
            $('#grand_kwt_dgn').val()
        );
        let piutang = grand - bayar;
        // biar gak minus
        if (piutang < 0) {
            piutang = 0;
        }
        $('#piutang_kwt_dgn').val(
            formatRupiah(piutang)
        );
    });
// ============================ End Of Jumlah Bayar Invoice Dingin ===============================
// =============================== Submit Kwitansi Dingin ===================================
    $('#proses_kwt_dgn').on('click', function() {
        let bayar = parseFloat($('#bayar_kwt_dgn').val());
        let top   = parseFloat($('#top_kwt_dgn').val());

        // Validasi BAYAR
        // if (isNaN(bayar) || bayar > 0) {
        //     alert('Nominal bayar harus angka dan lebih dari 0');
        //     $('#bayar_kwt_dgn').focus();
        //     return false;
        // }

        // Validasi TOP
        if (isNaN(top) || top < 0) {
            alert('TOP harus angka dan tidak boleh negatif');
            $('#top_kwt_dgn').focus();
            return false;
        }

        let data = {
            invoice: $('#no_faktur_kwt_dgn').val(),
            nosj: $('#nomor_sj_kwt_dgn').val(),
            bayar: $('#bayar_kwt_dgn').val(),
            top: $('#top_kwt_dgn').val(),
            tgl_jtp: $('#tgl_jtp_kwt_dgn').val()
        };
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('rentPendinginInvGen.store') }}",
                type: "POST",
                data: data,
                success: function(response) {

                    if (response.status) {
                        $('#loading_modal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                        $('#form_kwt_dgn').addClass('d-none');
                        $('#table_kwt_dgn').removeClass('d-none');
                        $('#DgnKwtTable').DataTable().ajax.reload();
                        // Cetak PDF
                        // window.open(response.redirect, '_blank');
                        printInvoiceRen(response.invoice);
                    } else {
                        $('#loading_modal').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }

                },
                error: function(xhr) {
                    $('#loading_modal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi Kesalahan Server !'
                    });
                }
            });
        });

    });
// ============================ End Of Submit Kwitansi Dingin ===============================
// =============================== Return Table Expedisi Generate =====================================
    $('#kembali_kwt_dgn, #returnInvDgnGenBtn').on('click', function () {
        // reset form
        clearAllKwtDgn();
        $('#form_kwt_dgn').addClass('d-none');
        $('#table_kwt_dgn').removeClass('d-none');
        table_kwt_dgn.ajax.reload();
    });
// ============================= End Of Return Table Expedisi Generate ================================
// ============================== Delete Kwitansi ================================
    $(document).on('click', '.btn-hapus-inv-gen-dgn', function() {
        var invoice = $(this).data('invoice');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Kwitansi Akan Dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loading_modal').modal('show');
                $('#loading_modal').one('shown.bs.modal', function () {
                    $.ajax({
                        url: "{{ route('rentPendinginKwitansi.destroy') }}",
                        type: 'POST',
                        data: {
                            invoice: invoice
                        },
                        success: function(response) {
                            $('#loading_modal').modal('hide');
                            Swal.fire('Terhapus!', response.success, 'success');
                            $('#DgnKwtTable').DataTable().ajax.reload();
                        }
                    });
                });
            }
        });
    });
// ============================== End Of Delete Kwitansi ================================
// ============================= Form Detail Edit Kwitansi Dingin ===============================
    $(document).on('click', '.btn-edit-inv-gen-dgn', function() {

        let nosj = $(this).data('nosj');

        // bisa ajax ambil detail invoice juga kalau mau
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('rentPendinginInvGen.show', ':kode') }}".replace(':kode', nosj),
                type: "GET",
                success: function(response) {

                    if (!response.status) {
                        $('#loading_modal').modal('hide');
                        alert(response.message);
                        return;
                    }
                    $('#loading_modal').modal('hide');
                    $('#table_kwt_dgn').addClass('d-none');
                    $('#form_kwt_dgn').removeClass('d-none');
                    // Clear Form Dulu
                    clearAllKwtDgn();

                    let d = response.data;
                    // ===== LEFT SIDE =====
                    $('#sub_total_kwt_dgn').val(formatRupiah(d.sub_total));
                    $('#d_charge_kwt_dgn').val(formatRupiah(d.d_charge));
                    $('#total_kwt_dgn').val(formatRupiah(d.total));

                    $('#disc_persen_kwt_dgn').val(d.disc_persen);
                    $('#disc_rp_kwt_dgn').val(formatRupiah(d.disc_rp));

                    $('#dpp_kwt_dgn').val(formatRupiah(d.total - d.disc_rp));
                    $('#ppn_persen_kwt_dgn').val(formatRupiah(d.ppn));
                    $('#grand_kwt_dgn').val(formatRupiah(d.grand));

                    $('#tgl_invoice_kwt_dgn').val(
                        d.tgl_invoice.substring(0,10)
                    );

                    // ===== RIGHT SIDE =====
                    $('#no_faktur_kwt_dgn').val(d.invoice);
                    $('#nomor_muat_kwt_dgn').val(d.nomor_muat);
                    $('#nomor_sj_kwt_dgn').val(d.nomor_sj);
                    $('#kendaraan_kwt_dgn').val(d.kendaraan);
                    $('#customer_kwt_dgn').val(d.customer);

                    // ===== BAYAR SECTION =====
                    $('#edit_mode_alert_dgn').fadeIn();
                    $('#kwt_exp_flag_dgn').val(1);
                    $('#bayar_kwt_dgn').val(formatRupiah(d.bayar));
                    $('#top_kwt_dgn').val(formatRupiah(d.saldo));
                    $('#tgl_jtp_kwt_dgn').val(d.tgl_jt ? d.tgl_jt.substring(0,10) : '');
                    $('#piutang_kwt_dgn').val(formatRupiah(d.grand - d.bayar));

                    $('#print_kwt_dgn, #pdf_kwt_dgn').show();
                },
                error: function(xhr) {
                    $('#loading_modal').modal('hide');
                    alert('Terjadi kesalahan server');
                }
            });
        });
    });

// ========================= End Of Form Detail Edit Kwitansi Dingin ==============================
// ============================== Cetak Pdf Rent Pendingin Invoice =================================
    $('#pdf_kwt_dgn').on('click', function(e) {
        e.preventDefault();
        var invoiceNo = $('#no_faktur_kwt_dgn').val();
        if (!invoiceNo) {
            alert('Nomor faktur tidak ditemukan!');
            return;
        }
        var url = "{{ route('rentPendinginGenerate.pdfGenerate', ['invoiceNo' => ':invoiceNo']) }}".replace(':invoiceNo', invoiceNo);
        window.open(url, '_blank');
    });
    // =========================== End Of Cetak Pdf Rent Pendingin Invoice ==============================
    // ============================== Cetak Rent Pendingin Invoice =================================
    $('#print_kwt_exp').on('click', function(e) {
        e.preventDefault();
        var invoiceNo = $('#no_faktur_kwt_dgn').val();
        printInvoiceRen(invoiceNo);
    });
    // =========================== End Of Cetak Rent Pendingin Invoice ==============================
});
// ########################################################################
// FUNCTION HELPER:
// ########################################################################
// Format Angka
function formatRupiah(angka) {
    if (!angka) return 0;

    return parseFloat(angka)
        .toLocaleString('id-ID');
}
// Unformat Angka
function unformatRupiah(angka) {

    if (!angka) return 0;

    return parseInt(
        angka.toString().replace(/\./g, '')
    ) || 0;
}
// Clear Form
function clearAllKwtDgn() {
    $('#edit_mode_alert_dgn').fadeOut();
    // ===== LEFT SIDE =====
    $('#sub_total_kwt_dgn').val('');
    $('#d_charge_kwt_dgn').val('');
    $('#total_kwt_dgn').val('');
    $('#disc_persen_kwt_dgn').val('');
    $('#disc_rp_kwt_dgn').val('');
    $('#dpp_kwt_dgn').val('');
    $('#ppn_persen_kwt_dgn').val('');
    $('#grand_kwt_dgn').val('');
    $('#tgl_invoice_kwt_dgn').val('');

    // ===== RIGHT SIDE =====
    $('#no_faktur_kwt_dgn').val('');
    $('#nomor_muat_kwt_dgn').val('');
    $('#nomor_sj_kwt_dgn').val('');
    $('#kendaraan_kwt_dgn').val('');
    $('#customer_kwt_dgn').val('');

    // ===== BAYAR SECTION =====
    $('#kwt_exp_flag_dgn').val('');
    $('#bayar_kwt_dgn').val('');
    $('#top_kwt_dgn').val('');
    $('#piutang_kwt_dgn').val('');

    // set ulang tanggal hari ini
    $('#tgl_jtp_kwt_dgn').val(new Date().toISOString().split('T')[0]);
}

// Fungsi Print Electron JS
function printInvoiceRen(invoiceNo){
    // 🔥 ROUTE INVOICE TEXT
    var url = "{{ route('expedisiInvoice.text', ['invoiceNo' => '__INVOICE__']) }}";
    url = url.replace('__INVOICE__', invoiceNo);
    // 🔥 AMBIL TEXT DARI LARAVEL
    $.get(url, function(res){
        // 🔥 KIRIM KE ELECTRON
        fetch('http://localhost:3000/print-text', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                text: res.text
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
}
</script>
