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
            <table class="table table-bordered table-striped" id="KwitansiDgnTable">
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
    // ============================== Tabel Invoice Generate Expedisi ===================================
    let tableKwitansi = $('#KwitansiDgnTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('pendinginKwitansi.data') }}",
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
    // ========================== End Of Tabel Invoice Generate Expedisi ===============================

    $(document).on('click', '.btn-proses-kwt-dgn', function (e) {
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
                    url: "{{ route('pendinginKwitansi.proses') }}",
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
                            $('#KwitansiDgnTable').DataTable().ajax.reload();
                            printKwitansiPendingin(kwitansi);
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

    $(document).on('click', '.btn-delete-kwt-dgn', function (e) {
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
                    url: "{{ route('pendinginKwitansi.destroy') }}",
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
                            $('#KwitansiDgnTable').DataTable().ajax.reload();
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

    $(document).on('click', '.btn-cetak-kwt-dgn', function (e) {
        e.preventDefault();
        let kwitansi = $(this).data('kwitansi');
        let pdfUrl = "{{ route('pendinginKwitansi.pdfKwitansi', ':kwitansi') }}";
        pdfUrl = pdfUrl.replace(':kwitansi', kwitansi);
        window.open(pdfUrl, '_blank');
    });

    $(document).on('click', '.btn-print-kwt-dgn', function (e) {
        e.preventDefault();
        printKwitansiPendingin($(this).data('kwitansi'));
    });

});

function printKwitansiPendingin(kwitansi) {
    let url = "{{ route('pendinginKwitansi.text', ['kwitansi' => '__KWITANSI__']) }}";
    url = url.replace('__KWITANSI__', encodeURIComponent(kwitansi));

    $.get(url, function (response) {
        fetch('http://localhost:3000/print-text', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: response.text })
        })
            .then(response => response.json())
            .then(response => console.log('PRINT KWITANSI RENT PENDINGIN:', response))
            .catch(error => {
                console.error('PRINT ERROR:', error);
                alert('Print service tidak aktif');
            });
    }).fail(function (xhr) {
        console.error('KWITANSI ERROR:', xhr.responseText);
        alert('Data kwitansi tidak ditemukan');
    });
}
</script>
