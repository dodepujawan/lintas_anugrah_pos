{{-- Bagian Tabel Expedisi Invoice Generate (Foremerly Kwitansi) --}}
<div class="container mt-3" id="table_kwt_exp">
    <div class="card p-3">
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tutorialInvoiceGenExpModal" aria-label="Buka panduan proses invoice Expedisi">
                <i class="bx bx-help-circle me-1"></i>Panduan Invoice
            </button>
        </div>
        <h5 class="text-center mb-3">FORM PROSES INVOICE EXPEDISI</h5>
        <form id="form-export-excel" action="{{ route('laporan.expedisiInvoiceGenerate.export') }}" method="POST">
        @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label d-block">
                        Status Invoice
                    </label>
                    <label>
                        <input type="radio" name="filter_inv_gen" value="belum" checked>
                        Belum Invoice
                    </label>
                    <label class="ms-3">
                        <input type="radio" name="filter_inv_gen" value="sudah">
                        Sudah Invoice
                    </label>
                </div>
                <label class="form-label">Export Laporan Excel</label>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" class="form-control" value="{{ date('Y-m-01') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_invoice_exp" id="customer_invoice_exp" class="form-control">
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success">
                        Export Excel
                    </button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table id="ExpInvGenTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Muat</th>
                        <th>Tgl Muat</th>
                        <th>No SJ</th>
                        <th>No GB</th>
                        <th>Customer</th>
                        <th>No Invoice</th>
                        <th>Tgl Invoice</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
{{-- Bagian Form Expedisi Kwitansi --}}
<div class="container-fluid mt-2 d-none" id="form_kwt_exp">
    <div class="card shadow-sm border-0">
        <!-- HEADER -->
        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color:#b7e1b0;">
            <button class="btn btn-sm btn-link text-decoration-none p-0" id="returnInvExpGenBtn" style="color:#107af3;">
                <i class='bx bx-chevron-left'></i> Kembali
            </button>
            <h5 class="mb-0 fw-bold">FORM PROSES INVOICE EXPEDISI</h5>
            <div style="width:80px"></div>
        </div>
        <div class="card-body p-3">
            <input type="hidden" id="kwt_exp_flag">
            <!-- ALERT -->
            <div id="edit_mode_alert" class="alert alert-warning text-center fw-bold py-2 mb-3" style="display:none;">
                ⚠ MODE EDIT KWITANSI !
            </div>
            <div class="row g-3">
                <!-- LEFT -->
                <div class="col-lg-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="small fw-bold">TGL MUAT</label>
                                    <input type="date" id="tgl_invoice_kwt_exp" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">SUB TOTAL</label>
                                    <input type="text" id="sub_total_kwt_exp" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">D.CHARGE</label>
                                    <input type="text" id="d_charge_kwt_exp" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">TOTAL</label>
                                    <input type="text" id="total_kwt_exp" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-3">
                                    <label class="small fw-bold">DISC %</label>
                                    <input type="text" id="disc_persen_kwt_exp" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-3">
                                    <label class="small fw-bold">DISC Rp</label>
                                    <input type="text" id="disc_rp_kwt_exp" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">DPP</label>
                                    <input type="text" id="dpp_kwt_exp" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">PPN %</label>
                                    <input type="text" id="ppn_persen_kwt_exp" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">GRAND TOTAL</label>
                                    <input type="text" id="grand_kwt_exp" class="form-control form-control-sm text-end fw-bold" readonly>
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
                                    <label class="small fw-bold">NO FAKTUR</label>
                                    <input type="text" id="no_faktur_kwt_exp" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">NOMOR MUAT</label>
                                    <input type="text" id="nomor_muat_kwt_exp" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">NOMOR SJ</label>
                                    <input type="text" id="nomor_sj_kwt_exp" class="form-control form-control-sm" readonly>
                                    <input type="hidden" id="nosj_kwt_exp">
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">KENDARAAN</label>
                                    <input type="text" id="kendaraan_kwt_exp" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">CUSTOMER</label>
                                    <input type="text" id="customer_kwt_exp" class="form-control form-control-sm" readonly>
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
                            <label class="small fw-bold">BAYAR</label>
                            <input type="text" id="bayar_kwt_exp" class="form-control form-control-sm text-end">
                        </div>
                        <div class="col-lg-2">
                            <label class="small fw-bold">TOP (HARI)</label>
                            <input type="number" id="top_kwt_exp" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-3">
                            <label class="small fw-bold">TGL JTP</label>
                            <input type="date" id="tgl_jtp_kwt_exp" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-4">
                            <label class="small fw-bold">PIUTANG</label>
                            <input type="text" id="piutang_kwt_exp" class="form-control form-control-sm text-end fw-bold" readonly>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-sm btn-primary" id="proses_kwt_exp">
                            PROSES
                        </button>
                        <button class="btn btn-sm btn-success" id="print_kwt_exp">
                            Print
                        </button>
                        <button class="btn btn-sm btn-info" id="pdf_kwt_exp">
                            Cetak Pdf
                        </button>
                        <button class="btn btn-sm btn-secondary" id="keluar_kwt_exp">
                            KELUAR
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="printTest()">
                            TEST DOT MATRIX
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('expedisiInvoieGen.expedisi-invoice-gen-modal')
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// ================================= Tabel Invoice Generate Expedisi =====================================
    if ($.fn.DataTable.isDataTable('#ExpInvGenTable')) {
        $('#ExpInvGenTable').DataTable().destroy();
    }
    let table_kwt = $('#ExpInvGenTable').DataTable({
        processing: true,
        serverSide: true,
        ajax:{
            url: "{{ route('expedisiInvoiceGenerate.data') }}",
            data: function (d) {
                d.status_invoice = $('input[name="filter_inv_gen"]:checked').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'NOMUAT', name: 'NOMUAT' },
            { data: 'TGLMUAT', name: 'TGLMUAT' },
            { data: 'NOSJ', name: 'NOSJ' },
            { data: 'GB', name: 'GB' },
            { data: 'CUSTOMER', name: 'CUSTOMER' },
            // { data: 'PIUTANG', name: 'PIUTANG', className: 'text-end' },
            { data: 'INVOICE', name: 'INVOICE' },
            { data: 'TGLINVOICE', name: 'TGLINVOICE' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Mengubah Radio Status Kwitansi
    $('input[name="filter_inv_gen"]').on('change', function() {

        // let status = $(this).val();

        // if(status === 'sudah'){
        //     table_kwt.column(5).visible(true); // tampilkan kolom No Kwitansi
        // } else {
        //     table_kwt.column(5).visible(false);
        // }

        table_kwt.ajax.reload();
    });
// ============================ End Of Tabel Invoice Generate Expedisi ================================
// =============================== Select Customer Expedisi ===================================
    $('#customer_invoice_exp').select2({
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
// ============================= Form Detail Invoice Generate Expedisi =================================
// ============================= Form Detail Invoice Generate Expedisi =================================
    $(document).on('click', '.btn-buat-invoice', function() {

        let surjalNo = $(this).data('surjal');

        // bisa ajax ambil detail invoice juga kalau mau
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('expedisiInvoiceGenerate.show', ':kode') }}".replace(':kode', surjalNo),
                type: "GET",
                success: function(response) {

                    if (!response.status) {
                        alert(response.message);
                        return;
                    }
                    $('#loading_modal').modal('hide');
                    $('#table_kwt_exp').addClass('d-none');
                    $('#form_kwt_exp').removeClass('d-none');
                    // Clear Form Dulu
                    clearAllKwtExp();

                    let d = response.data;
                    // ===== LEFT SIDE =====
                    $('#sub_total_kwt_exp').val(formatRupiah(d.sub_total));
                    $('#d_charge_kwt_exp').val(formatRupiah(d.d_charge));
                    $('#total_kwt_exp').val(formatRupiah(d.total));

                    $('#disc_persen_kwt_exp').val(d.disc_persen);
                    $('#disc_rp_kwt_exp').val(formatRupiah(d.disc_rp));

                    $('#dpp_kwt_exp').val(formatRupiah(d.total - d.disc_rp));
                    $('#ppn_persen_kwt_exp').val(formatRupiah(d.ppn));
                    $('#grand_kwt_exp').val(formatRupiah(d.grand));

                    $('#tgl_invoice_kwt_exp').val(
                        d.tgl_muat.substring(0,10)
                    );

                    // ===== RIGHT SIDE =====
                    $('#no_faktur_kwt_exp').val(d.invoice);
                    $('#nomor_muat_kwt_exp').val(d.nomor_muat);
                    $('#nomor_sj_kwt_exp').val(d.nomor_sj);
                    $('#nosj_kwt_exp').val(d.master_nosj);
                    $('#kendaraan_kwt_exp').val(d.kendaraan);
                    $('#customer_kwt_exp').val(d.customer);

                    // ===== BAYAR SECTION =====
                    $('#kwt_exp_flag').val(0);
                    $('#bayar_kwt_exp').val(formatRupiah(d.bayar));
                    $('#top_kwt_exp').val(formatRupiah(d.saldo));
                    $('#tgl_jtp_kwt_exp').val(d.tgl_jt ? d.tgl_jt.substring(0,10) : '');
                    $('#piutang_kwt_exp').val(formatRupiah(d.grand - d.bayar));

                    var invoiceNo = $('#no_faktur_kwt_exp').val();
                    if (invoiceNo != "") {
                        $('#print_kwt_exp, #pdf_kwt_exp').show();
                    } else {
                        $('#print_kwt_exp, #pdf_kwt_exp').hide();
                    }
                    // $('#modalKwitansiExp').modal('show');
                },
                error: function(xhr) {
                    $('#loading_modal').modal('hide');
                    alert('Terjadi kesalahan server');
                }
            });
        });
    });

// ============================ End Of Form Invoice Generate Expedisi ===============================
// =============================== Submit Kwitansi Expedisi ===================================
    $('#proses_kwt_exp').on('click', function() {
        let bayar = parseFloat($('#bayar_kwt_exp').val());
        let top   = parseFloat($('#top_kwt_exp').val());

        // Validasi BAYAR
        // if (isNaN(bayar) || bayar <= 0) {
        //     alert('Nominal bayar harus angka dan lebih dari 0');
        //     $('#bayar_kwt_exp').focus();
        //     return false;
        // }

        // Validasi TOP
        if (isNaN(top) || top < 0) {
            alert('TOP harus angka dan tidak boleh negatif');
            $('#top_kwt_exp').focus();
            return false;
        }

        let data = {
            invoice: $('#no_faktur_kwt_exp').val(),
            nosj: $('#nosj_kwt_exp').val(),
            bayar: $('#bayar_kwt_exp').val(),
            top: $('#top_kwt_exp').val(),
            tgl_jtp: $('#tgl_jtp_kwt_exp').val(),
            kwt_flag: $('#kwt_exp_flag').val()
        };
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            if ($('#no_faktur_kwt_exp').val() === '') {
                url = "{{ route('expedisiInvoiceGenerate.store') }}";
            } else {
                url = "{{ route('expedisiInvoiceGenerate.update') }}";
            }
            $.ajax({
                url: url,
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
                        $('#form_kwt_exp').addClass('d-none');
                        $('#table_kwt_exp').removeClass('d-none');
                        $('#ExpInvGenTable').DataTable().ajax.reload();
                        // Cetak PDF
                        // window.open(response.redirect, '_blank');
                        console.log('nilainya :' + response.invoiceNo)
                        printInvoice(response.invoiceNo);
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
// ============================ End Of Submit Kwitansi Expedisi ===============================
// =============================== Return Table Expedisi Generate =====================================
    $('#returnInvExpGenBtn').on('click', function () {
        // reset form
        clearAllKwtExp();
        $('#form_kwt_exp').addClass('d-none');
        $('#table_kwt_exp').removeClass('d-none');
        table_kwt.ajax.reload();
    });
    // ========================== End Of Return Table Expedisi Generate ==============================
    // ============================= Trigger Hitung Piutang ================================
    $(document).on('keyup change', '#bayar_kwt_exp', function () {
        let bayar = unformatRupiah(
            $('#bayar_kwt_exp').val()
        );
        let grand = unformatRupiah(
            $('#grand_kwt_exp').val()
        );
        let piutang = grand - bayar;
        $('#piutang_kwt_exp').val(
            formatRupiah(piutang)
        );
    });
    // ========================== End Of Trigger Hitung Piutang ==============================
    // ============================== Cetak Pdf Expedisi Invoice =================================
    $('#pdf_kwt_exp').on('click', function(e) {
        e.preventDefault();
        var invoiceNo = $('#no_faktur_kwt_exp').val();
        if (!invoiceNo) {
            alert('Nomor faktur tidak ditemukan!');
            return;
        }
        var url = "{{ route('expedisiInvoice.pdfInvoice', ['invoiceNo' => ':invoiceNo']) }}".replace(':invoiceNo', invoiceNo);
        window.open(url, '_blank');
    });
    // =========================== End Of Cetak Pdf Expedisi Invoice ==============================
    // ============================== Cetak Expedisi Invoice =================================
    $('#print_kwt_exp').on('click', function(e) {
        e.preventDefault();
        var invoiceNo = $('#no_faktur_kwt_exp').val();
        printInvoice(invoiceNo);
    });
    // =========================== End Of Cetak Expedisi Invoice ==============================
    // Fungsi Print Electron JS
   function printInvoice(invoiceNo){
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
// =========================== Form Detail Edit Kwitansi Expedisi (Expired) ==============================

    // $(document).on('click', '.btn-edit-kwt-exp', function() {

    //     let muatNo = $(this).data('invoice');

    //     // bisa ajax ambil detail invoice juga kalau mau
    //     $('#loading_modal').modal('show');
    //     $('#loading_modal').one('shown.bs.modal', function () {
    //         $.ajax({
    //             url: "{{ route('expedisiInvoiceGenerate.show', ':kode') }}".replace(':kode', muatNo),
    //             type: "GET",
    //             success: function(response) {

    //                 if (!response.status) {
    //                     alert(response.message);
    //                     return;
    //                 }
    //                 $('#loading_modal').modal('hide');
    //                 $('#table_kwt_exp').addClass('d-none');
    //                 $('#form_kwt_exp').removeClass('d-none');
    //                 // Clear Form Dulu
    //                 clearAllKwtExp();

    //                 let d = response.data;
    //                 // ===== LEFT SIDE =====
    //                 $('#sub_total_kwt_exp').val(formatRupiah(d.sub_total));
    //                 $('#d_charge_kwt_exp').val(formatRupiah(d.d_charge));
    //                 $('#total_kwt_exp').val(formatRupiah(d.total));

    //                 $('#disc_persen_kwt_exp').val(d.disc_persen);
    //                 $('#disc_rp_kwt_exp').val(formatRupiah(d.disc_rp));

    //                 $('#dpp_kwt_exp').val(formatRupiah(d.total - d.disc_rp));
    //                 $('#ppn_persen_kwt_exp').val(formatRupiah(d.ppn));
    //                 $('#grand_kwt_exp').val(formatRupiah(d.grand));

    //                 $('#tgl_invoice_kwt_exp').val(
    //                     d.tgl_invoice.substring(0,10)
    //                 );

    //                 // ===== RIGHT SIDE =====
    //                 $('#no_faktur_kwt_exp').val(d.invoice);
    //                 $('#nomor_muat_kwt_exp').val(d.nomor_muat);
    //                 $('#nomor_sj_kwt_exp').val(d.nomor_sj);
    //                 $('#kendaraan_kwt_exp').val(d.kendaraan);
    //                 $('#customer_kwt_exp').val(d.customer);

    //                 // ===== BAYAR SECTION =====
    //                 $('#edit_mode_alert').fadeIn();
    //                 $('#kwt_exp_flag').val(1);
    //                 $('#bayar_kwt_exp').val(formatRupiah(d.bayar));
    //                 $('#top_kwt_exp').val(formatRupiah(d.saldo));
    //                 $('#tgl_jtp_kwt_exp').val(d.tgl_jt);
    //                 $('#piutang_kwt_exp').val(formatRupiah(d.piutang));
    //                 // console.log('nyoba' + d.bayar + d.saldo);

    //                 // $('#modalKwitansiExp').modal('show');
    //             },
    //             error: function(xhr) {
    //                 $('#loading_modal').modal('hide');
    //                 alert('Terjadi kesalahan server');
    //             }
    //         });
    //     });
    // });

// ======================== End Of Form Detail Edit Kwitansi Expedisi ============================
// ============================== Delete Kwitansi (Expired) ================================
    // $(document).on('click', '.btn-hapus-kwt-exp', function() {
    //     var invoice = $(this).data('invoice');

    //     Swal.fire({
    //         title: 'Apakah Anda yakin?',
    //         text: "Kwitansi Akan Dihapus!",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#d33',
    //         cancelButtonColor: '#3085d6',
    //         confirmButtonText: 'Ya, hapus!',
    //         cancelButtonText: 'Batal'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $('#loading_modal').modal('show');
    //             $('#loading_modal').one('shown.bs.modal', function () {
    //                 $.ajax({
    //                     url: "{{ route('expedisiKwitansi.destroy') }}",
    //                     type: 'POST',
    //                     data: {
    //                         invoice: invoice
    //                     },
    //                     success: function(response) {
    //                         $('#loading_modal').modal('hide');
    //                         Swal.fire('Terhapus!', response.success, 'success');
    //                         $('#ExpInvGenTable').DataTable().ajax.reload();
    //                     }
    //                 });
    //             });
    //         }
    //     });
    // });
// ============================== End Of Delete Kwitansi ================================
// ============================== Click Return ================================
$(document).on('click', '#keluar_kwt_exp', function() {
    $('#form_kwt_exp').addClass('d-none');
    $('#table_kwt_exp').removeClass('d-none');
    $('#ExpInvGenTable').DataTable().ajax.reload();
});
// ============================== End Of Click Return ================================
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
function clearAllKwtExp() {
    $('#edit_mode_alert').fadeOut();
    // ===== LEFT SIDE =====
    $('#sub_total_kwt_exp').val('');
    $('#d_charge_kwt_exp').val('');
    $('#total_kwt_exp').val('');
    $('#disc_persen_kwt_exp').val('');
    $('#disc_rp_kwt_exp').val('');
    $('#dpp_kwt_exp').val('');
    $('#ppn_persen_kwt_exp').val('');
    $('#grand_kwt_exp').val('');
    $('#tgl_invoice_kwt_exp').val('');

    // ===== RIGHT SIDE =====
    $('#no_faktur_kwt_exp').val('');
    $('#nomor_muat_kwt_exp').val('');
    $('#nomor_sj_kwt_exp').val('');
    $('#nosj_kwt_exp').val(''),
    $('#kendaraan_kwt_exp').val('');
    $('#customer_kwt_exp').val('');

    // ===== BAYAR SECTION =====
    $('#kwt_exp_flag').val('');
    $('#bayar_kwt_exp').val('');
    $('#top_kwt_exp').val('');
    $('#piutang_kwt_exp').val('');

    // set ulang tanggal hari ini
    // $('#tgl_jtp_kwt_exp').val(new Date().toISOString().split('T')[0]);
}
// Ngtest Print
    function printTest() {
        $.get("{{ route('print.test') }}", function(res){
            fetch("http://localhost:3000/print-text", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    text: res.text
                })
            })
            .then(r => r.json())
            .then(r => {
                console.log(r);
            })
            .catch(err => {
                console.error(err);
                alert("Print service tidak aktif");
            });
        });

    }
</script>
