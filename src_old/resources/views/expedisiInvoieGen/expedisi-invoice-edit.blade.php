<style>
    #modal_invoice_edit label{
        font-size:11px;
    }

    #modal_invoice_edit .form-control-sm{
        height:28px;
        padding:2px 6px;
        font-size:12px;
    }

    #modal_invoice_edit textarea{
        font-size:12px;
    }

    #modal_invoice_edit .table{
        font-size:12px;
    }

    #modal_invoice_edit .input-group > .btn{
        padding: 0 .6rem !important;
    }
</style>
<div class="container-fluid py-2">
    {{-- ===================================================== --}}
    {{-- HEADER --}}
    {{-- ===================================================== --}}
    <div class="card shadow-sm border-0 mb-2">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold">INVOICE EXPEDISI</h4>
                <small class="text-muted">Data invoice expedisi EKS</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tutorialEditInvoiceExpModal" aria-label="Buka panduan edit invoice Expedisi">
                    <i class="bx bx-help-circle me-1"></i>Panduan
                </button>
                <button type="button" class="btn btn-dark btn-sm" id="btn_filter_invoice_eks">
                    <i class="fa fa-search"></i> Reload
                </button>
            </div>
        </div>
    </div>
    {{-- ===================================================== --}}
    {{-- FILTER --}}
    {{-- ===================================================== --}}
    <div class="card shadow-sm border-0 mb-2">
        <div class="card-body py-2">
            <div class="row g-2">
                <div class="col-lg-2">
                    <label class="form-label mb-1">DARI</label>
                    <input type="date" class="form-control form-control-sm" id="tanggal_dari_invoice_eks">
                </div>
                <div class="col-lg-2">
                    <label class="form-label mb-1">SAMPAI</label>
                    <input type="date" class="form-control form-control-sm" id="tanggal_sampai_invoice_eks">
                </div>
                <div class="col-lg-3">
                    <label class="form-label mb-1">CARI</label>
                    <input type="text" class="form-control form-control-sm" id="search_invoice_eks" placeholder="Invoice / Customer">
                </div>
            </div>
        </div>
    </div>
    {{-- ===================================================== --}}
    {{-- TABLE --}}
    {{-- ===================================================== --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm align-middle w-100" id="table_invoice_eks">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">NO</th>
                            <th width="110">TGL</th>
                            <th width="160">INVOICE</th>
                            <th>CUSTOMER</th>
                            <th width="160" class="text-end">GRAND</th>
                            <th width="160" class="text-end">PIUTANG</th>
                            <th width="160" class="text-end">BAYAR</th>
                            <th width="120">AKSI</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@include('expedisiInvoieGen.expedisi-invoice-edit-modal')
{{-- ===================================================== --}}
{{-- MODAL EDIT --}}
{{-- ===================================================== --}}
<div class="modal fade" id="modal_invoice_edit" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            {{-- ================================================= --}}
            {{-- HEADER --}}
            {{-- ================================================= --}}
            <div class="modal-header bg-success text-white py-2">
                <div>
                    <h5 class="modal-title fw-bold mb-0">EDIT INVOICE EXPEDISI</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            {{-- ================================================= --}}
            {{-- BODY --}}
            {{-- ================================================= --}}
            <div class="modal-body bg-light p-2">
                <div class="row g-2">
                    {{-- LEFT --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-2">
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">INVOICE</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="invoice_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">CUSTOMER</label>
                                    <input type="text" name="customer_invoice_edit_kd" id="customer_invoice_edit_kd" hidden>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="customer_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">KENDARAAN</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="kendaraan_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">DRIVER</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="driver_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">NO MUAT</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="nomuat_invoice_edit" readonly>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">TGL INV</label>
                                        <input type="date" class="form-control form-control-sm" id="tgl_invoice_invoice_edit">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">TGL JT</label>
                                        <input type="date" class="form-control form-control-sm" id="tgl_jt_invoice_edit">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">BAYAR</label>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bg-success-subtle" id="bayar_invoice_edit">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">PIUTANG</label>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bg-warning-subtle" id="piutang_invoice_edit" readonly>
                                    </div>
                                </div>
                                <!-- SIMPAN -->
                                <div class="col-mb-3 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm w-100" id="btn_simpan_invoice_edit">
                                        <i class='bx bx-save'></i>
                                        SIMPAN
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- RIGHT -->
                    <!-- ========================================= -->
                    <div class="col-lg-8">
                        <div class="row g-1">
                            <!-- WILAYAH -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">WILAYAH</label>
                                <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="wilayah_invoice_edit" readonly>
                            </div>
                            <!-- ITEM -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">ITEM</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="item_invoice_edit" readonly>
                                    <button class="btn btn-primary" type="button" id="btnItemGabungExpEdit"> <i class="bx bx-search"></i> </button>
                                </div>
                            </div>
                            <!-- RUTE -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">RUTE</label>
                                <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="rute_invoice_edit" readonly>
                            </div>
                            <!-- JUMLAH -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">JUMLAH</label>
                                <input type="text" class="form-control form-control-sm text-end bg-secondary-subtle" id="jumlah_invoice_edit">
                            </div>
                            <!-- HARGA -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">HARGA</label>
                                <input type="text" class="form-control form-control-sm text-end" id="harga_invoice_edit">
                            </div>
                            <!-- SUBTOTAL -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">SUBTOTAL</label>
                                <input type="text" class="form-control form-control-sm text-end" id="subtotal_invoice_edit" readonly>
                            </div>
                            <!-- DISC -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">DISC %</label>
                                <input type="text" class="form-control form-control-sm text-end" id="diskon_invoice_edit">
                            </div>
                            <!-- SUBTOTAL -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">TOTAL</label>
                                <input type="text" class="form-control form-control-sm text-end" id="total_invoice_edit" readonly>
                            </div>
                            <!-- DEL CHARGE -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">DEL.CHARGE</label>
                                <input type="text" class="form-control form-control-sm text-end" id="del_charge_invoice_edit">
                            </div>
                            <!-- PPN -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">PPN %</label>
                                <input type="text" class="form-control form-control-sm text-end" id="ppn_invoice_edit">
                            </div>
                            <!-- GRAND -->
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">GRAND</label>
                                <input type="text" class="form-control form-control-sm text-end fw-bold bg-danger-subtle" id="grand_invoice_edit">
                            </div>
                            <div>
                                <label class="form-label fw-bold mb-1">KETERANGAN</label>
                                <textarea class="form-control form-control-sm" rows="2" id="keterangan_invoice_edit"></textarea>
                            </div>
                        </div>
                        <hr class="my-2">
                        <!-- DETAIL SJ -->
                        <div style="height:220px;overflow-y:auto;">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-success">
                                    <tr>
                                        <th width="120">NO.SJ</th>
                                        <th width="120" class="text-end">JUMLAH</th>
                                        <th width="150" class="text-end">HARGA AW</th>
                                        <th width="150" class="text-end">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_detail_invoice_edit"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Item --}}
<div class="modal fade" id="itemModalInvEditExp" tabindex="-1">
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
                <table class="table table-bordered table-striped w-100" id="modalItemInvExpEditTable">
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
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// ========================================= Show Table ================================================
    // =====================================================
    // TABLE INVOICE
    // =====================================================
    let table_invoice_eks = $('#table_invoice_eks').DataTable({

        processing:true,
        serverSide:true,
        scrollX:true,
        pageLength:25,

        ajax:{
            url:'{{ route("expedisiInvoiceEdit.data") }}',
            data:function(d){
                d.tanggal_dari = $('#tanggal_dari_invoice_eks').val();
                d.tanggal_sampai = $('#tanggal_sampai_invoice_eks').val();
                d.search_invoice = $('#search_invoice_eks').val();
            }
        },
        columns:[
                    {
                        data:'DT_RowIndex',
                        name:'DT_RowIndex',
                        orderable:false,
                        searchable:false
                    },
                    {
                        data:'TGLINVOICE'
                    },
                    {
                        data:'INVOICE'
                    },
                    {
                        data:'CUSTOMER'
                    },
                    {
                        data:'GRAND',
                        className:'text-end'
                    },
                    {
                        data:'PIUTANG',
                        className:'text-end'
                    },
                    {
                        data:'bayar',
                        className:'text-end fw-bold text-success'
                    },
                    {
                        data:'aksi',
                        orderable:false,
                        searchable:false,
                        className:'text-center'
                    }
                ]
    });
    // =====================================================
    // FILTER
    // =====================================================
    $('#btn_filter_invoice_eks').click(function(){
        table_invoice_eks.ajax.reload();
    });
    // =====================================================
    // TABLE DETAIL
    // =====================================================
    let table_detail_invoice_edit = $('#table_detail_invoice_edit').DataTable({
        paging:false,
        searching:false,
        info:false,
        ordering:false,
        scrollY:'350px',
        scrollCollapse:true,
        columns:[
            {data:'nosj'},
            {data:'jumlah',className:'text-end'},
            {data:'harga',className:'text-end'},
            {data:'total',className:'text-end fw-bold'}
        ]
    });
// ==================================== End Of Show Table ===============================================
// ======================================== Show Invoice =================================================
    $(document).on('click','.btn_edit_invoice_eks', function(){
        let invoice = $(this).data('invoice');
        const route_show_invoice_edit = "{{ route('expedisiInvoiceEdit.show', ':invoice') }}";
        let url = route_show_invoice_edit.replace(':invoice', invoice);
        $.ajax({
            url:url,
            type:'GET',
            success:function(r){
                if(!r.status){Swal.fire('Error',r.message,'error');
                    return;
                }
                // Clear Form
                clearFormInvoiceEdit();
                let d = r.master;
                // =================================
                // MASTER
                // =================================
                $('#invoice_invoice_edit')
                    .val(d.invoice);
                $('#customer_invoice_edit_kd')
                    .val(d.customer_kode);
                $('#customer_invoice_edit')
                    .val(d.customer);
                $('#tgl_invoice_invoice_edit')
                    .val(d.tgl_invoice.substring(0,10));
                $('#tgl_jt_invoice_edit').val(
                    d.tgl_jt.substring(0,10)
                );
                $('#diskon_invoice_edit')
                    .val(d.diskon);
                $('#ppn_invoice_edit')
                    .val(d.ppn);
                $('#grand_invoice_edit')
                    .val(d.grand);
                $('#bayar_invoice_edit')
                    .val(d.bayar);
                $('#piutang_invoice_edit')
                    .val(d.piutang);
                $('#keterangan_invoice_edit')
                    .val(d.keterangan);
                $('#kendaraan_invoice_edit')
                .val(d.kendaraan);
                $('#driver_invoice_edit')
                    .val(d.driver);
                $('#nomuat_invoice_edit')
                    .val(d.nomuat);
                $('#wilayah_invoice_edit')
                    .val(d.wilayah);
                $('#item_invoice_edit')
                    .val(d.item);
                $('#rute_invoice_edit')
                    .val(d.rute);
                $('#jumlah_invoice_edit')
                    .val(parseFloat(d.jumlah));
                $('#harga_invoice_edit')
                    .val(d.harga);
                $('#subtotal_invoice_edit')
                    .val(parseFloat(d.jumlah) * d.harga);
                $('#diskon_invoice_edit')
                    .val(parseFloat(d.diskon));
                $('#total_invoice_edit')
                    .val(d.total);
                $('#del_charge_invoice_edit')
                    .val(d.del_charge);
                $('#ppn_invoice_edit')
                    .val(parseFloat(d.ppn));
                $('#grand_invoice_edit')
                    .val(d.grand);
                // =================================
                // DETAIL
                // =================================
                let html = '';
                r.details.forEach(function(d){
                    html += `
                        <tr>
                            <td>${d.nosj}</td>
                            <td class="text-end">
                                ${parseFloat(d.jumlah ?? 0).toLocaleString()}
                            </td>
                            <td class="text-end">
                                ${parseFloat(d.hargaaw ?? 0).toLocaleString()}
                            </td>
                            <td class="text-end fw-bold">
                                ${parseFloat(d.total ?? 0).toLocaleString()}
                            </td>
                        </tr>
                    `;
                });
                $('#tbody_detail_invoice_edit').html(html);
                // =================================
                // SHOW MODAL
                // =================================
                $('#modal_invoice_edit').modal('show');
            }
        });
    });
// ==================================== End Of Show Invoice ===============================================
// =================================== Pilih Item =====================================
    $(document).on('click', '#btnItemGabungExpEdit', function(e) {
        var expedisiId = $('#customer_invoice_edit_kd').val();

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

        $('#itemModalInvEditExp').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalItemInvExpEditTable')) {
            $('#modalItemInvExpEditTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalItemInvExpEditTable').DataTable({
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
        var rute = row.find('td:eq(4)').text();
        var harga = row.find('td:eq(5)').text().trim();

        // Mengisi nilai ke elemen yang dituju
        $('#item_invoice_edit').val(pesanan);
        $('#rute_invoice_edit').val(rute);
        $('#harga_invoice_edit').val(harga);
        hitungInvoiceEdit();
        // Tutup modal
        $('#itemModalInvEditExp').modal('hide');
    });
    // =============================== End Of Pilih Item ==================================
// ====================================== Hitung Jumlah ================================================
$(document).on(
    'keyup change',
    '#jumlah_invoice_edit,#harga_invoice_edit,#diskon_invoice_edit,#del_charge_invoice_edit,#ppn_invoice_edit,#bayar_invoice_edit',
    function(){
        hitungInvoiceEdit();
    }
);
// ==================================== End Of Hitung Jumlah ============================================
// ======================================= Submit Invoice ================================================
    // SIMPAN EDIT INVOICE
    $(document).on('click', '#btn_simpan_invoice_edit', function() {
        let invoice = $('#invoice_invoice_edit').val();
        if (!invoice) {
            Swal.fire('Oops...', 'Invoice tidak ditemukan', 'error');
            return;
        }

        const route_update_invoice_edit = "{{ route('expedisiInvoiceEdit.update', ':invoice') }}";
        let url = route_update_invoice_edit.replace(':invoice', invoice);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                invoice: invoice,
                item: $('#item_invoice_edit').val(),
                rute: $('#rute_invoice_edit').val(),
                bayar: parseNominalExpInvoiceEdit($('#bayar_invoice_edit').val()),
                harga: parseNominalExpInvoiceEdit($('#harga_invoice_edit').val()),
                disc: $('#diskon_invoice_edit').val(),
                del_charge: parseNominalExpInvoiceEdit($('#del_charge_invoice_edit').val()),
                ppn: $('#ppn_invoice_edit').val(),
                tgl_jt: $('#tgl_jt_invoice_edit').val(),
                jumlah: $('#jumlah_invoice_edit').val(),
                keterangan: $('#keterangan_invoice_edit').val()
            },
            beforeSend: function() {
                $('#btn_simpan_invoice_edit').prop('disabled', true).html("<i class='bx bx-loader-alt bx-spin'></i> MENYIMPAN...");
            },
            success: function(r) {
                $('#btn_simpan_invoice_edit').prop('disabled', false).html("<i class='bx bx-save'></i> SIMPAN");
                Swal.fire({ icon: 'success', title: 'Berhasil', text: r.message });
                table_invoice_eks.ajax.reload(null, false);
                $('#modal_invoice_edit').modal('hide');
            },
            error: function(xhr) {
                $('#btn_simpan_invoice_edit').prop('disabled', false).html("<i class='bx bx-save'></i> SIMPAN");
                let message = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Oops...', text: message });
            }
        });
    });
// ===================================== End Of Submit Invoice =============================================
});
// ************************************* Helper ***********************************************************
function parseNominalExpInvoiceEdit(value) {
    const text = String(value ?? '').trim();
    const sign = text.startsWith('-') ? -1 : 1;
    const digits = text.replace(/\D/g, '');

    return sign * (parseInt(digits, 10) || 0);
}

function clearFormInvoiceEdit()
{
    // =====================================
    // IDENTITAS
    // =====================================
    $('#invoice_invoice_edit').val('');
    $('#customer_invoice_edit_kd').val('');
    $('#customer_invoice_edit').val('');

    $('#kendaraan_invoice_edit').val('');
    $('#driver_invoice_edit').val('');
    $('#nomuat_invoice_edit').val('');

    // =====================================
    // TANGGAL
    // =====================================
    $('#tgl_invoice_invoice_edit').val('');
    $('#tgl_jt_invoice_edit').val('');

    // =====================================
    // INFO EXPEDISI
    // =====================================
    $('#wilayah_invoice_edit').val('');
    $('#item_invoice_edit').val('');
    $('#rute_invoice_edit').val('');

    // =====================================
    // NILAI
    // =====================================
    $('#jumlah_invoice_edit').val('');

    $('#harga_invoice_edit').val('');
    $('#subtotal_invoice_edit').val('');
    $('#total_invoice_edit').val('');

    $('#diskon_invoice_edit').val('');
    $('#del_charge_invoice_edit').val('');

    $('#ppn_invoice_edit').val('');
    $('#grand_invoice_edit').val('');

    // =====================================
    // PEMBAYARAN
    // =====================================
    $('#bayar_invoice_edit').val('');
    $('#piutang_invoice_edit').val('');

    // =====================================
    // KETERANGAN
    // =====================================
    $('#keterangan_invoice_edit').val('');

    // =====================================
    // DETAIL SJ
    // =====================================
    $('#tbody_detail_invoice_edit').html('');
}

function hitungInvoiceEdit() {
    let jumlah = parseFloat($('#jumlah_invoice_edit').val()) || 0;
    let harga = parseNominalExpInvoiceEdit($('#harga_invoice_edit').val());
    let disc = parseFloat($('#diskon_invoice_edit').val()) || 0;
    let delCharge = parseNominalExpInvoiceEdit($('#del_charge_invoice_edit').val());
    let ppnPersen = parseFloat($('#ppn_invoice_edit').val()) || 0;
    let bayar = parseNominalExpInvoiceEdit($('#bayar_invoice_edit').val());

    let subTotal = jumlah * harga;
    let discAmount = subTotal * (disc / 100);
    let total = subTotal - discAmount;
    let ppnNominal = total * (ppnPersen / 100);
    let grand = total + ppnNominal + delCharge;
    let piutang = grand - bayar;

    $('#subtotal_invoice_edit').val(Math.round(subTotal).toLocaleString('id-ID'));
    $('#total_invoice_edit').val(Math.round(total).toLocaleString('id-ID'));
    $('#grand_invoice_edit').val(Math.round(grand).toLocaleString('id-ID'));
    $('#piutang_invoice_edit').val(Math.round(piutang).toLocaleString('id-ID'));
}
</script>
