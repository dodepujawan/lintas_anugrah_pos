{{-- ============================= --}}
{{-- TABLE COOLROOM --}}
{{-- ============================= --}}
<div class="container mt-3" id="table_coolroom">
    <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">
                TRANSAKSI COOLROOM
            </h5>
            <button class="btn btn-primary btn-sm" id="btn_tambah_coolroom">
                <i class='bx bx-plus'></i> TRANSAKSI BARU
            </button>
        </div>
        <div class="table-responsive">
            <table id="CoolroomTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No SJ</th>
                        <th>Tgl SJ</th>
                        <th>Customer</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Grand</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- FORM COOLROOM --}}
{{-- ============================= --}}
<div class="container-fluid mt-2 d-none" id="form_coolroom">
    <div class="card shadow-sm border-0">
        {{-- HEADER --}}
        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color:#b7e1b0;">
            <button class="btn btn-sm btn-link text-decoration-none p-0" id="returnCoolroomBtn" style="color:#107af3;">
                <i class='bx bx-chevron-left'></i> Kembali
            </button>
            <h5 class="mb-0 fw-bold">
                FORM TRANSAKSI COOLROOM
            </h5>
            <button type="button" class="btn btn-sm btn-outline-primary" style="width:80px" data-bs-toggle="modal" data-bs-target="#tutorialCoolroomModal" aria-label="Buka panduan transaksi Coolroom">
                <i class="bx bx-help-circle me-1"></i>Panduan
            </button>
        </div>

        <div class="card-body p-3">
            <input type="hidden" id="coolroom_id">
            <div class="row g-3">
                {{-- LEFT --}}
                <div class="col-lg-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small fw-bold">NO SJ</label>
                                    <input type="text" id="nosj_coolroom" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">TGL SJ</label>
                                    <input type="date" id="tglsj_coolroom" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">
                                        CUSTOMER
                                    </label>
                                    <div class="input-group input-group-sm mb-2">
                                        <input type="hidden" id="customer_coolroom_id" name="customer_coolroom_id">
                                        <input type="text" class="form-control" id="customer_coolroom" name="customer_coolroom" readonly placeholder="Pilih customer...">
                                        <button class="btn btn-outline-primary" id="customer_coolroom_btn" type="button">
                                            <i class="bx bx-search"></i>
                                        </button>
                                    </div>
                                    {{-- CUSTOMER KODE --}}
                                    <div class="customer-kode-info">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark me-2">
                                                Kode:
                                            </span>
                                            <input type="text"
                                                class="form-control form-control-sm border-0 bg-transparent" id="customer_kode_coolroom" name="customer_kode_coolroom" readonly style="font-weight:600;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">JUMLAH KG</label>
                                    <input type="number" step="0.001" id="jumlah_coolroom" class="form-control form-control-sm text-end">
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">HARGA @</label>
                                    <input type="text" id="harga_coolroom" class="form-control form-control-sm text-end">
                                </div>
                                <div class="col-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="boxing_coolroom">
                                        <label class="form-check-label small fw-bold" for="boxing_coolroom">
                                            Harga Boxing (tekan spasi to check)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">KETERANGAN</label>
                                    <textarea id="keterangan_coolroom" class="form-control form-control-sm" rows="2"></textarea>
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
                                    <input type="text" id="subtotal_coolroom" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">DISC %</label>
                                    <input type="number" id="disc_coolroom" class="form-control form-control-sm text-end" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">DISC Rp</label>
                                    <input type="text" id="ndisc_coolroom" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">DPP</label>
                                    <input type="text" id="dpp_coolroom" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">PPN %</label>
                                    <input type="number" id="ppn_coolroom" class="form-control form-control-sm text-end">
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">PPN Rp</label>
                                    <input type="text" id="nppn_coolroom" class="form-control form-control-sm text-end" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold">GRAND TOTAL</label>
                                    <input type="text" id="grand_coolroom" class="form-control form-control-sm text-end fw-bold" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ACTION --}}
            <div class="card border-0 bg-secondary-subtle mt-3">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-sm btn-primary" id="proses_coolroom">
                            SIMPAN
                        </button>
                        <button class="btn btn-warning btn-sm px-4 d-none" id="btnPrintSuratJalanCool" type="button" data-id="">
                            <i class="bx bx-printer me-1"></i>surat jalan PDF
                        </button>
                        <button class="btn btn-sm btn-secondary" id="keluar_coolroom">
                            KELUAR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Customer --}}
<div class="modal fade" id="customerModalCool" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal pelanggan"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped w-100" id="modalCusCoolTable">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Jenis Usaha</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('coolroom.coolroom-modal')
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// ================================ Table Colroom ======================================
    let coolroomTable = $('#CoolroomTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('coolroom.getData') }}"
        },
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'NOSJ',
                name: 'NOSJ'
            },
            {
                data: 'TGLSJ',
                name: 'TGLSJ'
            },
            {
                data: 'CUSTOMER',
                name: 'CUSTOMER'
            },
            {
                data: 'JUMLAH',
                name: 'JUMLAH',
                className: 'text-end'
            },
            {
                data: 'HARGA',
                name: 'HARGA',
                className: 'text-end'
            },
            {
                data: 'GRAND',
                name: 'GRAND',
                className: 'text-end fw-bold'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]

    });
// ============================== End Of Table Colroom ====================================
// ============================== Show Form Colroom ====================================
    $('#btn_tambah_coolroom').on('click', function(){
        $('#table_coolroom').addClass('d-none');
        $('#form_coolroom').removeClass('d-none');
        clearCoolroomForm();
        // Generae PPN
        loadInputPajak();
        setButtonCoolMode('save');
    });
// ============================== End Of Show Form Colroom ====================================
// ================================== Show Table Colroom =====================================
    $('#returnCoolroomBtn, #keluar_coolroom').on('click', function(){
        $('#form_coolroom').addClass('d-none');
        $('#table_coolroom').removeClass('d-none');
        coolroomTable.ajax.reload();
    });
// ============================== End Of Show Table Colroom ====================================
// ================================= Pilih Customer =====================================
    // ### Select Customer
    $('#customer_coolroom_btn').click(function(e) {
        e.preventDefault();
        $('#customerModalCool').modal('show');
        // destroy kalau sudah ada
        if ($.fn.DataTable.isDataTable('#modalCusCoolTable')) {
            $('#modalCusCoolTable')
                .DataTable()
                .destroy();
        }
        let table = $('#modalCusCoolTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("coolroom-cus.data") }}',
            scrollX: true,
            pageLength: 10,
            autoWidth: false,
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    width: '56px'
                },
                {
                    data: 'kode_cus',
                    name: 'kode_cus',
                    width: '135px'
                },
                {
                    data: 'NAMACUST',
                    name: 'NAMACUST',
                    width: '250px'
                },
                {
                    data: 'TYPECUST',
                    name: 'TYPECUST',
                    width: '150px'
                },
                {
                    data: 'TELEPON',
                    name: 'TELEPON',
                    width: '150px'
                },
                {
                    data: 'EMAIL',
                    name: 'EMAIL',
                    width: '220px'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    width: '68px'
                }
            ]
        });

        $('#customerModalCool').one('shown.bs.modal', function() {
            table.columns.adjust();
        });
    });
    // ### Pick Customer
    $(document).on('click', '.view-btn-customer-coolroom', function(){
            var kodeCus = $(this).data('id');
            var namaCus = $(this).data('name');
            var CUSTOMER = $(this).data('customer');
            // Mengisi nilai ke elemen yang dituju
            $('#customer_coolroom_id').val(kodeCus);
            $('#customer_coolroom').val(namaCus);
            $('#customer_kode_coolroom').val(CUSTOMER);
            // tutup modal
            $('#customerModalCool').modal('hide');
            $('#jumlah_coolroom').focus();
        }
    );
// ============================== End Of Pilih Customer ====================================
// ============================== AUTO FORMAT HARGA ====================================
    // ### HARGA
    $(document).on('keyup', '#harga_coolroom',
        function(){
            let angka = unformatRupiah(
                $(this).val()
            );
            $(this).val(
                formatRupiah(angka)
            );
        }
    );

    // ### DISC
    $(document).on('keyup','#disc_coolroom',
        function(){
            if ($(this).val() == '') {
                $(this).val(0);
            }
        }
    );

    // ### Hitung Coolroom
    function hitungCoolroom()
    {
        // =========================
        // AMBIL VALUE
        // =========================
        let jumlah = parseFloat(
            $('#jumlah_coolroom').val()
        ) || 0;
        let harga = unformatRupiah(
            $('#harga_coolroom').val()
        );
        let discPersen = parseFloat(
            $('#disc_coolroom').val()
        ) || 0;
        let ppnPersen = parseFloat(
            $('#ppn_coolroom').val()
        ) || 0;
        let boxing = $('#boxing_coolroom').is(':checked');
        // =========================
        // SUBTOTAL
        // =========================
        let subtotal = 0;
        // harga boxing
        if (boxing) {
            subtotal = harga;
        } else {
            subtotal = jumlah * harga;
        }

        // =========================
        // DISC RP
        // =========================
        let ndisc = subtotal * discPersen / 100;
        // =========================
        // DPP
        // =========================
        let dpp = subtotal - ndisc;
        // =========================
        // PPN RP
        // =========================
        let nppn = dpp * ppnPersen / 100;
        // =========================
        // GRAND TOTAL
        // =========================
        let grand = dpp + nppn;
        // =========================
        // RENDER
        // =========================
        $('#subtotal_coolroom').val(
            formatRupiah(
                Math.round(subtotal)
            )
        );
        $('#ndisc_coolroom').val(
            formatRupiah(
                Math.round(ndisc)
            )
        );
        $('#dpp_coolroom').val(
            formatRupiah(
                Math.round(dpp)
            )
        );
        $('#nppn_coolroom').val(
            formatRupiah(
                Math.round(nppn)
            )
        );
        $('#grand_coolroom').val(
            formatRupiah(
                Math.round(grand)
            )
        );
    }
    // ### Auto Hitung
    $(document).on(
        'keyup change', '#jumlah_coolroom, #harga_coolroom, #disc_coolroom, #ppn_coolroom, #boxing_coolroom',
        function(){
            hitungCoolroom();
        }
    );
// ============================== End Of AUTO FORMAT HARGA ====================================
// ====================================== Pajak PPN ====================================
    function loadInputPajak(){
        $.ajax({
            url: '{{ route('get_pajak') }}',
            type: 'GET',
            success: function(response) {
                let nilai_ppn = response.data.ppn;
                $('#ppn_coolroom').val(nilai_ppn);
            },
            error: function() {
                $('#ppn_coolroom').val('Error Loading');
            }
        });
    }
// =================================== End Of Pajak PPN ==================================
// =================================== Submit Coolroom ==================================

    $('#proses_coolroom').on('click',function(e){
        e.preventDefault();
        console.log(
            $('#boxing_coolroom').is(':checked')
        );
        let id=$('#coolroom_id').val();
        let url=id ? "{{ route('coolroom.update',['id'=>'__id__']) }}".replace('__id__',id) : "{{ route('coolroom.store') }}";
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url:url,
                type:"POST",
                data:{
                    tglsj:$('#tglsj_coolroom').val(),
                    customer:$('#customer_coolroom').val(),
                    customer_kode:$('#customer_kode_coolroom').val(),
                    jumlah:$('#jumlah_coolroom').val(),
                    harga:$('#harga_coolroom').val(),
                    boxing:$('#boxing_coolroom').is(':checked'),
                    disc:$('#disc_coolroom').val(),
                    ppn:$('#ppn_coolroom').val(),
                    keterangan:$('#keterangan_coolroom').val()
                },
                success:function(res){
                    Swal.fire({
                        icon:'success',
                        title:'Berhasil',
                        text:res.message
                    });
                    // printSuratJalanCool(res.nosj);
                    // window.open("{{ route('coolroom.pdf',['nosj'=>'__NOSJ__']) }}".replace('__NOSJ__',res.nosj), '_blank');
                    $('#loading_modal').modal('hide');
                    $('#form_coolroom').addClass('d-none');
                    $('#table_coolroom').removeClass('d-none');
                    $('#CoolroomTable').DataTable().ajax.reload(null,false);
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
// =================================== End Of Submit Coolroom ==================================
// =================================== Edit Coolroom ==================================
    $(document).on('click', '.btn-edit-coolroom',function(){
            let id=$(this).data('id');
            $.ajax({
                url:"{{ route('coolroom.edit',['id'=>'__id__']) }}".replace('__id__',id),
                type:"GET",
                success:function(d){
                    // =====================
                    // SHOW FORM
                    // =====================
                    $('#table_coolroom').addClass('d-none');
                    $('#form_coolroom').removeClass('d-none');
                    // =====================
                    // SET VALUE
                    // =====================
                    $('#coolroom_id').val(d.id);
                    $('#nosj_coolroom').val(d.NOSJ);
                    $('#tglsj_coolroom').val(d.TGLSJ);
                    $('#customer_coolroom').val(d.CUSTOMER);
                    $('#customer_kode_coolroom').val(d.CUSTOMER_KODE);
                    $('#jumlah_coolroom').val(Number(d.JUMLAH));
                    $('#harga_coolroom').val(formatRupiah(d.HARGA));
                    $('#boxing_coolroom').prop('checked',d.BOXING);
                    $('#disc_coolroom').val(d.DISC);
                    $('#ppn_coolroom').val(d.PPN);
                    $('#keterangan_coolroom').val(d.KETERANGAN);
                    // =====================
                    // TOTAL
                    // =====================
                    $('#subtotal_coolroom').val(formatRupiah(d.SUBTOTAL)
                    );
                    $('#ndisc_coolroom').val((d.NDISC)
                    );
                    $('#dpp_coolroom').val(formatRupiah(d.DPP)
                    );
                    $('#nppn_coolroom').val((d.NPPN)
                    );
                    $('#grand_coolroom').val(formatRupiah(d.GRAND)
                    );

                    $('#btnPrintSuratJalanCool').removeClass('d-none');
                    $('#btnPrintSuratJalanCool').attr('data-sj',d.NOSJ);
                    setButtonCoolMode('update');
                }
            });

        }
    );
// =================================== End Of Edit Coolroom ==================================
// =================================== Delete Coolroom ==================================
    $(document).on('click', '.btn-delete-coolroom', function(){
            let id=$(this).data('id');
            Swal.fire({
                title:'Hapus Data?',
                text:'Data coolroom akan dihapus',
                icon:'warning',
                showCancelButton:true,
                confirmButtonText:'Ya, Hapus',
                cancelButtonText:'Batal'
            }).then((result)=>{
                if(result.isConfirmed){
                    $('#loading_modal').modal('show');
                    $('#loading_modal').one('shown.bs.modal', function () {
                        $.ajax({
                            url:"{{ route('coolroom.destroy',['id'=>'__id__']) }}".replace('__id__',id),
                            type:"DELETE",
                            data:{
                                _token:"{{ csrf_token() }}"
                            },
                            success:function(res){
                                $('#loading_modal').modal('hide');
                                Swal.fire({
                                    icon:'success',
                                    title:'Berhasil',
                                    text:res.message
                                });
                                $('#CoolroomTable').DataTable().ajax.reload(null,false);
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
                }
            });
        }
    );
// =================================== End Of Delete Coolroom ==================================
// =================================== Enter Next Input Coolroom ==================================
    $(document).ready(function() {
        var fields = [
            '#nosj_coolroom','#tglsj_coolroom','#customer_coolroom',
            '#customer_kode_coolroom','#jumlah_coolroom','#harga_coolroom',
            '#boxing_coolroom','#keterangan_coolroom','#subtotal_coolroom',
            '#disc_coolroom','#ndisc_coolroom','#dpp_coolroom',
            '#ppn_coolroom','#nppn_coolroom','#grand_coolroom'
        ];

        // Enter untuk pindah field
        $(fields.join(',')).on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var idx = fields.indexOf('#' + $(this).attr('id'));
                for (var i = idx + 1; i < fields.length; i++) {
                    var f = $(fields[i]);
                    if (f.is(':visible') && !f.prop('disabled') && !f.prop('readonly')) {
                        f.focus();
                        f.select();
                        return;
                    }
                }
                $('#proses_coolroom').focus();
            }
        });

        // Cehckbox Bisa toggle dengan Enter dan Spasi
        $('#boxing_coolroom').on('keydown', function(e) {
            if (e.key === ' ') {
                e.preventDefault();
                $(this).prop('checked', !$(this).prop('checked'));
                $(this).trigger('change');
            }
        });

        // Tombol SIMPAN & KELUAR
        $('#proses_coolroom, #keluar_coolroom').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).click();
            }
        });
    });
// ================================ End Of Enter Next Input Coolroom ===============================
// =========================== Print PDF ================================
    $('#btnPrintSuratJalanCool').on('click', function () {
        let sj = $(this).attr('data-sj');
        let url = "{{ route('coolroom.pdf', ':sj') }}";
        url = url.replace(':sj', sj);
        window.open(url, '_blank');
    });
// ======================== End Of Print PDF =============================
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

    function clearCoolroomForm()
    {
        $('#coolroom_id').val('');
        $('#nosj_coolroom').val('');
        $('#tglsj_coolroom').val(
            '{{ date("Y-m-d") }}'
        );
        $('#customer_coolroom_id').val('');
        $('#customer_coolroom').val('');
        $('#customer_kode_coolroom').val('');
        $('#jumlah_coolroom').val('');
        $('#harga_coolroom').val('');
        $('#boxing_coolroom').prop(
            'checked',
            false
        );
        $('#disc_coolroom').val(0);
        $('#ppn_coolroom').val('');
        $('#subtotal_coolroom').val('');
        $('#ndisc_coolroom').val('');
        $('#dpp_coolroom').val('');
        $('#nppn_coolroom').val('');
        $('#grand_coolroom').val('');
        $('#keterangan_coolroom').val('');
    }

    function setButtonCoolMode(mode) {
        let btn = $('#proses_coolroom');
        if (mode === 'update') {
            btn.text('UPDATE')
            .removeClass('btn-primary')
            .addClass('btn-success');
        } else {
            btn.text('SIMPAN')
            .removeClass('btn-success')
            .addClass('btn-primary');
            let btnPdf = $('#btnPrintSuratJalanCool');
            btnPdf.addClass('d-none');
        }
    }

    // ###Print PDF
    function printSuratJalanCool(sj) {
        let url = "{{ route('coolroom.pdf', ':sj') }}";
        url = url.replace(':sj', sj);
        fetch("http://localhost:3000/print", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                url: url,
                printer: "EPSONLX"
            })
        })
        .then(r => r.json())
        .then(r => console.log(r))
        .catch(err => {
            console.error(err);
            alert("Print service tidak aktif");
        });
    }
// ++++++++++++++++++++++++++++++++++++ End Of Helper ++++++++++++++++++++++++++++++++++++++++++
</script>
