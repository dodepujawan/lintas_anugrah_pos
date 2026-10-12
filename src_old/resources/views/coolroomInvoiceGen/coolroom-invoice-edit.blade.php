{{-- ===================================================== --}}
{{-- HEADER --}}
{{-- ===================================================== --}}
<div class="card shadow-sm border-0 mb-2">
    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 fw-bold">INVOICE COOLROOM</h4>
            <small class="text-muted">Data invoice Coolroom</small>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tutorialCoolroomEditInvoiceModal" aria-label="Buka panduan edit invoice Coolroom">
            <i class="bx bx-help-circle me-1"></i>Panduan
        </button>
    </div>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body p-2">
        <div class="row g-2 mb-2">
            <div class="col-lg-2">
                <label class="form-label mb-1">DARI</label>
                <input type="date" class="form-control form-control-sm" id="tanggal_dari_invoice_coolroom">
            </div>
            <div class="col-lg-2">
                <label class="form-label mb-1">SAMPAI</label>
                <input type="date" class="form-control form-control-sm" id="tanggal_sampai_invoice_coolroom">
            </div>
            <div class="col-lg-3">
                <label class="form-label mb-1">CARI</label>
                <input type="text" class="form-control form-control-sm" id="search_invoice_coolroom">
            </div>
            <div class="col-lg-2 d-flex align-items-end">
                <button class="btn btn-dark btn-sm" id="btn_filter_invoice_cool">Reload</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle w-100" id="table_invoice_coolroom">
                <thead class="table-dark">
                    <tr>
                        <th width="50">NO</th>
                        <th width="120">TGL</th>
                        <th width="160">INVOICE</th>
                        <th>CUSTOMER</th>
                        <th width="150" class="text-end">GRAND</th>
                        <th width="150" class="text-end">PIUTANG</th>
                        <th width="150" class="text-end">BAYAR</th>
                        <th width="100">AKSI</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_invoice_edit_coolroom" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title mb-0">EDIT INVOICE COOLROOM</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <!-- KIRI -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body p-2">
                                <div class="mb-2">
                                    <label class="fw-bold small mb-1 d-block">INVOICE</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="invoice_invoice_edit_coolroom" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="fw-bold small mb-1 d-block">CUSTOMER</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="customer_invoice_edit_coolroom" readonly>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="fw-bold small mb-1 d-block">TGL INV</label>
                                        <input type="date" class="form-control form-control-sm" id="tgl_invoice_invoice_edit_coolroom">
                                    </div>
                                    <div class="col-6">
                                        <label class="fw-bold small mb-1 d-block">TGL JT</label>
                                        <input type="date" class="form-control form-control-sm" id="tgl_jt_invoice_edit_coolroom">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="fw-bold small mb-1 d-block">BAYAR</label>
                                        <input type="text" class="form-control form-control-sm text-end bg-success-subtle" id="bayar_invoice_edit_coolroom">
                                    </div>
                                    <div class="col-6">
                                        <label class="fw-bold small mb-1 d-block">PIUTANG</label>
                                        <input type="text" class="form-control form-control-sm text-end bg-warning-subtle" id="piutang_invoice_edit_coolroom" readonly>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-sm w-100" id="btn_simpan_invoice_edit_coolroom">SIMPAN</button>
                            </div>
                        </div>
                    </div>
                    <!-- KANAN -->
                    <div class="col-lg-8">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">BOXING</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="boxing_invoice_edit_coolroom">
                                    <label class="form-check-label">Ya</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">JUMLAH</label>
                                <input type="text" class="form-control form-control-sm text-end" id="jumlah_invoice_edit_coolroom">
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">UNIT</label>
                                <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="unit_invoice_edit_coolroom" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">HARGA</label>
                                <input type="text" class="form-control form-control-sm text-end" id="harga_invoice_edit_coolroom">
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">SUBTOTAL</label>
                                <input type="text" class="form-control form-control-sm text-end bg-secondary-subtle" id="subtotal_invoice_edit_coolroom" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">DISC %</label>
                                <input type="text" class="form-control form-control-sm text-end" id="diskon_invoice_edit_coolroom">
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">DPP</label>
                                <input type="text" class="form-control form-control-sm text-end bg-secondary-subtle" id="dpp_invoice_edit_coolroom" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 d-block">PPN %</label>
                                <input type="text" class="form-control form-control-sm text-end" id="ppn_invoice_edit_coolroom">
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold small mb-1 d-block">TOTAL</label>
                                <input type="text" class="form-control form-control-sm text-end bg-secondary-subtle" id="total_invoice_edit_coolroom" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold small mb-1 d-block">GRAND</label>
                                <input type="text" class="form-control form-control-sm text-end fw-bold bg-danger-subtle" id="grand_invoice_edit_coolroom" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="fw-bold small mb-1 d-block">KETERANGAN</label>
                                <textarea class="form-control form-control-sm" rows="2" id="keterangan_invoice_edit_coolroom"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('coolroomInvoiceGen.coolroom-invoice-edit-model')
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// ====================================== End Of Show Table ================================================
    let tableInvoiceCoolroom = $('#table_invoice_coolroom').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ordering: false,
        ajax: {
            url: "{{ route('coolroomInv.tableEdit') }}",
            data: function(d) {
                d.tanggal_dari = $('#tanggal_dari_invoice_coolroom').val();
                d.tanggal_sampai = $('#tanggal_sampai_invoice_coolroom').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false },
            { data: 'TGLINVOICE', name: 'TGLINVOICE' },
            { data: 'INVOICE', name: 'INVOICE' },
            { data: 'CUSTOMER', name: 'CUSTOMER' },
            { data: 'GRAND', name: 'GRAND', className: 'text-end' },
            { data: 'PIUTANG', name: 'PIUTANG', className: 'text-end' },
            { data: 'bayar', name: 'bayar', searchable: false, className: 'text-end' },
            { data: 'aksi', name: 'aksi', searchable: false, orderable: false }
        ]
    });
    $('#btn_filter_invoice_cool').click(function () {
        tableInvoiceCoolroom.search($('#search_invoice_coolroom').val().trim()).draw();
    });
// =========================================== Show Table =================================================
// =======================================  Show Invoice ==================================================
    $(document).on('click', '.btn_edit_invoice_coolroom', function() {
        clearFormInvoiceEditCoolroom();

        let invoice = $(this).data('invoice');
        let url = "{{ route('coolroomInv.showEdit', ':invoice') }}".replace(':invoice', invoice);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
                if (!res.status) {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    return;
                }

                let m = res.master;

                $('#invoice_invoice_edit_coolroom').val(m.invoice);
                $('#customer_invoice_edit_coolroom').val(m.customer);
                $('#tgl_invoice_invoice_edit_coolroom').val(m.tgl_invoice ?? '');
                $('#tgl_jt_invoice_edit_coolroom').val(m.tgl_jt ?? '');
                $('#boxing_invoice_edit_coolroom').prop('checked', m.boxing == 1);
                $('#jumlah_invoice_edit_coolroom').val(parseFloat(m.jumlah));
                $('#unit_invoice_edit_coolroom').val(m.unit);
                $('#harga_invoice_edit_coolroom').val(formatRupiah(m.harga));
                $('#subtotal_invoice_edit_coolroom').val(formatRupiah(m.subtotal));
                $('#diskon_invoice_edit_coolroom').val(parseFloat(m.disc));
                $('#dpp_invoice_edit_coolroom').val(formatRupiah(m.dpp));
                $('#ppn_invoice_edit_coolroom').val(parseFloat(m.ppn));
                $('#total_invoice_edit_coolroom').val(formatRupiah(m.total));
                $('#grand_invoice_edit_coolroom').val(formatRupiah(m.grand));
                $('#bayar_invoice_edit_coolroom').val(formatRupiah(m.bayar));
                $('#piutang_invoice_edit_coolroom').val(formatRupiah(m.piutang));
                $('#keterangan_invoice_edit_coolroom').val(m.keterangan);

                $('#modal_invoice_edit_coolroom').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
                });
            }
        });
    });
// ==================================== End Of Show Invoice ===============================================
// ====================================== HitungInvoice =================================================
$(document).on('keyup', '#jumlah_invoice_edit_coolroom, #harga_invoice_edit_coolroom, #diskon_invoice_edit_coolroom, #ppn_invoice_edit_coolroom, #bayar_invoice_edit_coolroom', function() {
    hitungInvoiceEditCoolroom();
});

$(document).on('change', '#boxing_invoice_edit_coolroom', function() {
    hitungInvoiceEditCoolroom();
});
// ==================================== End Of Hitung Invoice ===============================================
// ======================================= Submit Invoice =================================================
    $('#btn_simpan_invoice_edit_coolroom').click(function() {
        $.ajax({
            url: "{{ route('coolroomInv.updateEdit') }}",
            type: 'POST',
            data: {
                invoice: $('#invoice_invoice_edit_coolroom').val(),
                tgl_jt: $('#tgl_jt_invoice_edit_coolroom').val(),
                boxing: $('#boxing_invoice_edit_coolroom').is(':checked'),
                jumlah: $('#jumlah_invoice_edit_coolroom').val(),
                bayar: unformatRupiah($('#bayar_invoice_edit_coolroom').val()),
                harga: unformatRupiah($('#harga_invoice_edit_coolroom').val()),
                disc: $('#diskon_invoice_edit_coolroom').val(),
                ppn: $('#ppn_invoice_edit_coolroom').val(),
                keterangan: $('#keterangan_invoice_edit_coolroom').val()
            },
            success: function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                $('#modal_invoice_edit_coolroom').modal('hide');
                tableInvoiceCoolroom.ajax.reload(null, false);
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message ?? 'Terjadi kesalahan' });
            }
        });
    });
// ==================================== End Of Submit Invoice ==============================================
});
// ################################ HELPER ##############################################
// Format Angka
function formatRupiah(angka) {
    if (!angka) return 0;

    return parseFloat(angka)
        .toLocaleString('id-ID');
}
// Unformat Angka
function unformatRupiah(angka) {

    if (!angka) return 0;

    const value = angka.toString().trim();
    const sign = value.startsWith('-') ? -1 : 1;
    const digits = value.replace(/\D/g, '');

    return sign * (parseInt(digits, 10) || 0);
}

function clearFormInvoiceEditCoolroom()
{
    $('#invoice_invoice_edit_coolroom').val('');
    $('#customer_invoice_edit_coolroom').val('');

    $('#tgl_invoice_invoice_edit_coolroom').val('');
    $('#tgl_jt_invoice_edit_coolroom').val('');

    $('#boxing_invoice_edit_coolroom')
        .prop('checked', false);

    $('#jumlah_invoice_edit_coolroom').val('');
    $('#unit_invoice_edit_coolroom').val('');

    $('#harga_invoice_edit_coolroom').val('');
    $('#subtotal_invoice_edit_coolroom').val('');

    $('#diskon_invoice_edit_coolroom').val('');

    $('#dpp_invoice_edit_coolroom').val('');

    $('#ppn_invoice_edit_coolroom').val('');

    $('#total_invoice_edit_coolroom').val('');
    $('#grand_invoice_edit_coolroom').val('');

    $('#bayar_invoice_edit_coolroom').val('');
    $('#piutang_invoice_edit_coolroom').val('');

    $('#keterangan_invoice_edit_coolroom').val('');
}

function hitungInvoiceEditCoolroom() {
    let boxing = $('#boxing_invoice_edit_coolroom').is(':checked');
    let jumlah = parseFloat($('#jumlah_invoice_edit_coolroom').val()) || 0;
    let harga = unformatRupiah($('#harga_invoice_edit_coolroom').val());
    let disc = parseFloat($('#diskon_invoice_edit_coolroom').val()) || 0;
    let ppn = parseFloat($('#ppn_invoice_edit_coolroom').val()) || 0;
    let bayar = unformatRupiah($('#bayar_invoice_edit_coolroom').val());

    let subtotal = boxing ? harga : jumlah * harga;
    let ndisc = subtotal * disc / 100;
    let dpp = subtotal - ndisc;
    let nppn = dpp * ppn / 100;
    let grand = dpp + nppn;
    let piutang = grand - bayar;

    $('#subtotal_invoice_edit_coolroom').val(formatRupiah(Math.round(subtotal)));
    $('#dpp_invoice_edit_coolroom').val(formatRupiah(Math.round(dpp)));
    $('#total_invoice_edit_coolroom').val(formatRupiah(Math.round(grand)));
    $('#grand_invoice_edit_coolroom').val(formatRupiah(Math.round(grand)));
    $('#piutang_invoice_edit_coolroom').val(formatRupiah(Math.round(piutang)));
}
</script>
