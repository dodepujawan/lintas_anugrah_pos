<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-3">
                <select id="filter_status_kwt" class="form-control">
                    <option value="belum">Belum Kwitansi</option>
                    <option value="sudah">Sudah Kwitansi</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="KwitansiEksTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Tgl Invoice</th>
                        <th>Customer</th>
                        <th>Grand</th>
                        <th>Piutang</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
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
    // ================================= Tabel Invoice Generate Expedisi =====================================
    let tableKwitansi = $('#KwitansiEksTable').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('expedisiKwitansi.data') }}",
            data: function (d) {
                d.status_kwt = $('#filter_status_kwt').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'INVOICE', name: 'INVOICE'},
            {data: 'TGLINVOICE', name: 'TGLINVOICE'},
            {data: 'CUSTOMER', name: 'CUSTOMER'},
            {data: 'GRAND', name: 'GRAND', className: 'text-end'},
            {data: 'PIUTANG', name: 'PIUTANG', className: 'text-end'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });
    // ==========================================
    // FILTER STATUS
    // ==========================================
    $('#filter_status_kwt').on('change', function () {
        tableKwitansi.ajax.reload();
    });
    // ============================== End Of Tabel Invoice Generate Expedisi ==================================
    $(document).on('click', '.btn-proses-kwt', function (e) {
        e.preventDefault();
        let invoice = $(this).data('invoice');
        Swal.fire({
            title: 'Proses Kwitansi?',
            text: 'Kwitansi akan dibuat untuk invoice ' + invoice,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'

        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('expedisiKwitansi.proses') }}",
                    type: "POST",
                    data: {
                        invoice: invoice
                    },
                    success: function (response) {
                        if (response.status) {
                            let kwitansi = response.nokwt;
                            Swal.fire({

                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            $('#KwitansiEksTable').DataTable().ajax.reload();
                            // PDF
                            // let pdfUrl = "{{ route('expedisiKwitansi.pdfKwitansi', ':kwitansi') }}";
                            // pdfUrl = pdfUrl.replace(':kwitansi', kwitansi);
                            // window.open(pdfUrl, '_blank');
                            printKwitansi(kwitansi);

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan server'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-delete-kwt', function (e) {
        e.preventDefault();
        let kwt = $(this).data('kwitansi');
        Swal.fire({
            title: 'Batalkan Kwitansi?',
            text: 'Kwitansi ' + kwt + ' akan dibatalkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('expedisiKwitansi.destroy') }}",
                    type: "POST",
                    data: {
                        kwt: kwt
                    },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            $('#KwitansiEksTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan server'
                        });
                    }
                });
            }
        });
    });

    // ============================== Print Expedisi Kwitansi =================================
    $(document).on('click', '.btn-print-kwt', function (e) {
        e.preventDefault();
        var kwitansi = $(this).data('kwitansi');
        printKwitansi(kwitansi);
    });
    // =========================== End Of Print Expedisi Kwitansi ==============================

    $(document).on('click', '.btn-cetak-kwt', function (e) {
        e.preventDefault();
        let kwitansi = $(this).data('kwitansi');
        let pdfUrl = "{{ route('expedisiKwitansi.pdfKwitansi', ':kwitansi') }}";
            pdfUrl = pdfUrl.replace(':kwitansi', kwitansi);
            window.open(pdfUrl, '_blank');
    });

});

function printKwitansi(kwitansi) {
    // ROUTE KWITANSI TEXT
    var url = "{{ route('expedisiKwitansi.text', ['kwitansi' => '__KWITANSI__']) }}";
    url = url.replace('__KWITANSI__', encodeURIComponent(kwitansi));

    // AMBIL TEXT DARI LARAVEL
    $.get(url, function (res) {
        // KIRIM KE ELECTRON
        fetch('http://localhost:3000/print-text', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: res.text })
        })
            .then(res => res.json())
            .then(res => {
                console.log("🚀 PRINT KWITANSI:", res);
            })
            .catch(err => {
                console.log("❌ ERROR:", err);
                alert("Print service tidak aktif");
            });
    }).fail(function (xhr) {
        console.log("❌ ERROR LARAVEL:", xhr.responseText);
        alert("Data kwitansi tidak ditemukan");
    });
}
</script>
