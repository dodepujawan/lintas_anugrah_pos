{{-- ============================= --}}
{{-- TABLE INVOICE COOLROOM --}}
{{-- ============================= --}}
<div class="container mt-3" id="table_coolroom_inv">
    <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">
                INVOICE COOLROOM
            </h5>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tutorialCoolroomInvoiceModal" aria-label="Buka panduan Invoice Coolroom">
                <i class="bx bx-help-circle me-1"></i>Panduan
            </button>
        </div>

        <div class="row mb-3">
            <form id="form-export-coolroom" action="{{ route('coolroomInv.export') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">
                            Status Invoice
                        </label>
                        <label>
                            <input type="radio" name="filter_inv_coolroom" value="belum" checked>
                            Belum Invoice
                        </label>

                        <label class="ms-3">
                            <input type="radio" name="filter_inv_coolroom" value="sudah">
                            Sudah Invoice
                        </label>
                    </div>

                    <label class="form-label">
                        Export Excel Coolroom
                    </label>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Tanggal Dari
                        </label>
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ ('Y-m-01') }}" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Tanggal Sampai
                        </label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_invoice_col" id="customer_invoice_col" class="form-control">
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
            <table id="CoolroomInvoiceTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No SJ</th>
                        <th>Tgl SJ</th>
                        <th>Customer</th>
                        <th>Jumlah</th>
                        <th>Grand</th>
                        <th>Invoice</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- FORM INVOICE COOLROOM --}}
{{-- ============================= --}}
<div class="container-fluid mt-2 d-none" id="form_coolroom_inv">
    <div class="card shadow-sm border-0">
        {{-- HEADER --}}
        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color:#cfe2ff;">
            <button class="btn btn-sm btn-link text-decoration-none p-0" id="returnCoolroomInvBtn" style="color:#107af3;">
                <i class='bx bx-chevron-left'></i> Kembali
            </button>
            <h5 class="mb-0 fw-bold">
                FORM INVOICE COOLROOM
            </h5>
            <div style="width:80px"></div>
        </div>
        <div class="card-body p-3">
            <input type="hidden" id="coolroom_inv_id">
            <div class="row g-3">
                {{-- LEFT --}}
                <div class="col-lg-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small fw-bold">NO SJ</label>
                                    <input type="text" id="nosj_coolroom_inv" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">TGL SJ</label>
                                    <input type="date" id="tglsj_coolroom_inv" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">NO INVOICE</label>
                                    <input type="text" id="invoice_coolroom_inv" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">TGL INVOICE</label>
                                    <input type="date" id="tglinvoice_coolroom_inv" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">
                                        CUSTOMER
                                    </label>
                                    <div class="input-group input-group-sm mb-2">
                                        <input type="hidden" id="customer_coolroom_inv_id" name="customer_coolroom_inv_id">
                                        <input type="text" class="form-control" id="customer_coolroom_inv" name="customer_coolroom_inv" readonly>
                                    </div>
                                    <div class="customer-kode-info">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark me-2">
                                                Kode:
                                            </span>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent" id="customer_kode_coolroom_inv" name="customer_kode_coolroom_inv" readonly style="font-weight:600;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">JUMLAH</label>
                                    <input type="text" id="jumlah_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">HARGA</label>
                                    <input type="text" id="harga_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- RIGHT --}}
                <div class="col-lg-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small fw-bold">SUBTOTAL</label>
                                    <input type="text" id="subtotal_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">DISC %</label>
                                    <input type="text" id="disc_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">DISC Rp</label>
                                    <input type="text" id="ndisc_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">DPP</label>
                                    <input type="text" id="dpp_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">PPN %</label>
                                    <input type="text" id="ppn_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">PPN Rp</label>
                                    <input type="text" id="nppn_coolroom_inv" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">GRAND TOTAL</label>
                                    <input type="text" id="grand_coolroom_inv" class="form-control form-control-sm text-end fw-bold" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- PAYMENT --}}
            <div class="card border-0 bg-secondary-subtle mt-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-3">
                            <label class="small fw-bold">BAYAR</label>
                            <input type="text" id="bayar_coolroom_inv" class="form-control form-control-sm text-end">
                        </div>
                        <div class="col-lg-2">
                            <label class="small fw-bold">TOP</label>
                            <input type="number" id="top_coolroom_inv" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-3">
                            <label class="small fw-bold">TGL JTP</label>
                            <input type="date" id="tgljt_coolroom_inv" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-4">
                            <label class="small fw-bold">PIUTANG</label>
                            <input type="text" id="piutang_coolroom_inv" class="form-control form-control-sm text-end fw-bold" readonly>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-sm btn-primary" id="proses_coolroom_inv">
                            PROSES INVOICE
                        </button>
                        <button class="btn btn-sm btn-primary" id="print_coolroom_inv">
                            PRINT
                        </button>
                        <button class="btn btn-sm btn-secondary" id="pdf_coolroom_inv">
                            PDF
                        </button>
                        <button class="btn btn-sm btn-secondary" id="keluar_coolroom_inv">
                            KELUAR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('coolroomInvoiceGen.coolroom-invoice-gen-modal')
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// ================================ Table Colroom Invoice ======================================
    let CoolroomInvoiceTable=$('#CoolroomInvoiceTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url:"{{ route('coolroomInv.getData') }}",
            data:function(d){
                d.status_invoice=$(
                    'input[name="filter_inv_coolroom"]:checked'
                ).val();
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
                data:'NOSJ',
                name:'NOSJ'
            },
            {
                data:'TGLSJ',
                name:'TGLSJ'
            },
            {
                data:'CUSTOMER',
                name:'CUSTOMER'
            },
            {
                data:'JUMLAH',
                name:'JUMLAH',
                className:'text-end'
            },
            {
                data:'GRAND',
                name:'GRAND',
                className:'text-end fw-bold'
            },
            {
                data:'INVOICE',
                name:'INVOICE',
                defaultContent:'-'
            },
            {
                data:'action',
                name:'action',
                orderable:false,
                searchable:false
            }
        ]
    });
    // =====================================
    // FILTER STATUS
    // =====================================
    $(document).on( 'change', 'input[name="filter_inv_coolroom"]',
        function(){
            $('#CoolroomInvoiceTable').DataTable().ajax.reload();
        }
    );
// ============================= End Of Table Colroom Invoice ===================================
// =============================== Select Customer Expedisi ===================================
    $('#customer_invoice_col').select2({
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
// ============================= Buat Colroom Invoice ===================================
    $(document).on('click','.btn-proses-invoice-coolroom',
        function(){
            let nosj=$(this).data('nosj');
            $.ajax({
                url:"{{ route('coolroomInv.show',['nosj'=>'__NOSJ__']) }}".replace('__NOSJ__',nosj),
                type:"GET",
                success:function(res){
                    let d=res.data;
                    // =====================
                    // SHOW FORM
                    // =====================
                    $('#table_coolroom_inv').addClass('d-none');
                    $('#form_coolroom_inv').removeClass('d-none');
                    // =====================
                    // IDENTITAS
                    // =====================
                    $('#coolroom_inv_id').val(d.id);
                    $('#nosj_coolroom_inv').val(d.nosj);
                    $('#tglsj_coolroom_inv').val(d.tglsj?.split('T')[0] ?? '');
                    $('#invoice_coolroom_inv').val(d.invoice);
                    $('#tglinvoice_coolroom_inv').val(d.tgl_invoice?.split('T')[0] ?? '');
                    $('#customer_coolroom_inv').val(d.customer);
                    $('#customer_kode_coolroom_inv').val(d.customer_kode);
                    // =====================
                    // NILAI
                    // =====================
                    $('#jumlah_coolroom_inv')
                        .val(Number(d.jumlah));
                    $('#harga_coolroom_inv')
                        .val(formatRupiah(d.harga));
                    $('#subtotal_coolroom_inv')
                        .val(formatRupiah(d.sub_total));
                    $('#disc_coolroom_inv')
                        .val(d.disc_persen);
                    $('#ndisc_coolroom_inv')
                        .val(formatRupiah(d.disc_rp));
                    $('#dpp_coolroom_inv')
                        .val(formatRupiah(d.dpp));
                    $('#ppn_coolroom_inv')
                        .val(d.ppn);
                    $('#nppn_coolroom_inv')
                        .val(formatRupiah(d.ppn_rp));
                    $('#grand_coolroom_inv')
                        .val(formatRupiah(d.grand));
                    // =====================
                    // PEMBAYARAN
                    // =====================
                    $('#bayar_coolroom_inv')
                        .val(formatRupiah(d.bayar));
                    $('#piutang_coolroom_inv')
                        .val(formatRupiah(d.grand));
                    $('#top_coolroom_inv')
                        .val(d.top);
                    $('#tgljt_coolroom_inv')
                        .val(d.tgl_jt);

                    $('#print_coolroom_inv, #pdf_coolroom_inv').hide();
                }
            });
        }
    );
// ========================== End Of Buat Colroom Invoice =================================
// ============================= Edit Colroom Invoice ===================================
    $(document).on('click', '.btn-edit-invoice-coolroom',
        function(){
            let nosj=$(this).data('nosj');
            $.ajax({
                url:"{{ route('coolroomInv.show',['nosj'=>'__NOSJ__']) }}"
                    .replace('__NOSJ__',nosj),
                type:"GET",
                success:function(res){
                    let d=res.data;
                    // =====================
                    // SHOW FORM
                    // =====================
                    $('#table_coolroom_inv')
                        .addClass('d-none');
                    $('#form_coolroom_inv')
                        .removeClass('d-none');
                    // =====================
                    // IDENTITAS
                    // =====================
                    $('#coolroom_inv_id').val(d.id);
                    $('#nosj_coolroom_inv')
                        .val(d.nosj);
                    $('#tglsj_coolroom_inv').val(d.tglsj?.split('T')[0] ?? '');
                    $('#invoice_coolroom_inv')
                        .val(d.invoice);
                    $('#tglinvoice_coolroom_inv').val(d.tgl_invoice?.split('T')[0] ?? '');
                    $('#customer_coolroom_inv')
                        .val(d.customer);
                    $('#customer_kode_coolroom_inv')
                        .val(d.customer_kode);
                    // =====================
                    // NILAI
                    // =====================
                    $('#jumlah_coolroom_inv')
                        .val(Number(d.jumlah));
                    $('#harga_coolroom_inv')
                        .val(formatRupiah(d.harga));
                    $('#subtotal_coolroom_inv')
                        .val(formatRupiah(d.sub_total));
                    $('#disc_coolroom_inv')
                        .val(d.disc_persen);
                    $('#ndisc_coolroom_inv')
                        .val(formatRupiah(d.disc_rp));
                    $('#dpp_coolroom_inv')
                        .val(formatRupiah(d.dpp));
                    $('#ppn_coolroom_inv')
                        .val(d.ppn);
                    $('#nppn_coolroom_inv')
                        .val(formatRupiah(d.ppn_rp));
                    $('#grand_coolroom_inv')
                        .val(formatRupiah(d.grand));
                    // =====================
                    // PEMBAYARAN
                    // =====================
                    $('#bayar_coolroom_inv')
                        .val(formatRupiah(d.bayar));
                    $('#piutang_coolroom_inv')
                        .val(formatRupiah(d.piutang));
                    $('#top_coolroom_inv')
                        .val(d.top);
                    $('#tgljt_coolroom_inv')
                        .val(d.tgl_jt ? d.tgl_jt.substring(0,10) : '');

                    $('#print_coolroom_inv, #pdf_coolroom_inv').show();
                }
            });
        }
    );
// ========================== End Of Edit Colroom Invoice =================================
// =============================== Submit Colroom Invoice =================================
    $('#proses_coolroom_inv').on('click',function(e){
        e.preventDefault();
        let invoice=$('#invoice_coolroom_inv').val();
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url:"{{ route('coolroomInv.proses') }}",
                type:"POST",
                data:{
                    _token:"{{ csrf_token() }}",
                    invoice:invoice,
                    nosj:$('#nosj_coolroom_inv').val(),
                    bayar:$('#bayar_coolroom_inv').val(),
                    top:$('#top_coolroom_inv').val(),
                    tgl_jtp:$('#tgljt_coolroom_inv').val()
                },
                success:function(res){
                    $('#loading_modal')
                        .modal('hide');
                    Swal.fire({
                        icon:'success',
                        title:'Berhasil',
                        text:res.message
                    });

                    // =====================
                    // RELOAD PDF
                    // =====================
                    // if(res.invoice){
                    //     window.open(
                    //         "{{ route('coolroomInv.pdf',['invoice'=>'__INV__']) }}"
                    //             .replace('__INV__',res.invoice),
                    //         '_blank'
                    //     );
                    // }
                    printInvoiceCol(res.invoice);
                    // =====================
                    // RESET
                    // =====================
                    $('#form_coolroom_inv').addClass('d-none');
                    $('#table_coolroom_inv').removeClass('d-none');
                    $('#CoolroomInvoiceTable').DataTable().ajax.reload(null,false);
                },

                error:function(xhr){
                    $('#loading_modal').modal('hide');
                    Swal.fire({
                        icon:'error',
                        title:'Error',
                        text:xhr.responseJSON.message
                    });
                }
            });
        });
    });
// =============================== End Of Submit Colroom Invoice ===============================
// ============================== Cetak Pdf Expedisi Invoice =================================
    $('#pdf_coolroom_inv').on('click', function(e) {
        e.preventDefault();
        var invoiceNo = $('#invoice_coolroom_inv').val();
        if (!invoiceNo) {
            alert('Nomor faktur tidak ditemukan!');
            return;
        }
        var url = "{{ route('coolroomInv.pdf', ['invoice' => ':invoiceNo']) }}".replace(':invoiceNo', invoiceNo);
        window.open(url, '_blank');
    });
// =========================== End Of Cetak Pdf Expedisi Invoice ==============================
// ============================== Cetak Expedisi Invoice =================================
    $('#print_coolroom_inv').on('click', function(e) {
        e.preventDefault();
        var invoiceNo = $('#invoice_coolroom_inv').val();
        printInvoiceCol(invoiceNo);
    });
// =========================== End Of Cetak Expedisi Invoice ==============================
// =============================== Keluar Colroom Invoice =================================
    $('#returnCoolroomInvBtn,#keluar_coolroom_inv').on('click', function(){
            $('#form_coolroom_inv').addClass('d-none');
            $('#table_coolroom_inv').removeClass('d-none');
        }
    );
// ========================== End Of Edit Keluar Invoice =================================
// ================================= Hitung Piutang =====================================
$(document).on('keyup', '#bayar_coolroom_inv',
    function(){
        let angka=unformatRupiah(
            $(this).val()
        );
        $(this).val(
            formatRupiah(angka)
        );
        hitungInvoiceCoolroom();
    }
);
// ========================== End Of Hitung Piutang =================================
});
// ++++++++++++++++++++++++++++++++++++++++ Helper ++++++++++++++++++++++++++++++++++++++++++
    function formatRupiah(angka)
    {
        angka = angka.toString();
        return angka.replace(
            /\B(?=(\d{3})+(?!\d))/g,
            "."
        );
    }

    function unformatRupiah(angka)
    {
        if (!angka) return 0;
        return parseInt(
            angka.toString().replace(/\./g, '')
        ) || 0;
    }

    // =====================================
    // HITUNG FORM INVOICE
    // =====================================
    function hitungInvoiceCoolroom()
    {
        let grand=unformatRupiah(
            $('#grand_coolroom_inv').val()
        );

        let bayar=unformatRupiah(
            $('#bayar_coolroom_inv').val()
        );

        // =====================
        // VALIDASI
        // =====================
        if(bayar>grand){

            bayar=grand;

            $('#bayar_coolroom_inv')
                .val(formatRupiah(grand));

        }

        if(bayar<0){

            bayar=0;

        }

        // =====================
        // PIUTANG
        // =====================
        let piutang=grand-bayar;

        // =====================
        // SET PIUTANG
        // =====================
        $('#piutang_coolroom_inv')
            .val(formatRupiah(piutang));

    }
    // FNGSI DIRCT PRINT
    function printInvoiceCol(invoiceNo){
    // 🔥 ROUTE INVOICE TEXT
    var url = "{{ route('coolroomInv.text', ['invoiceNo' => '__INVOICE__']) }}";
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
