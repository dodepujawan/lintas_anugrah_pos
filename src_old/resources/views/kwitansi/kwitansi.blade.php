<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Data Kwitansi</h4>
                </div>
                <div class="card-body">
                    <form id="form-export-kwitansi" action="{{ route('kwitansi.export') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Status Kwitansi</label>
                                <select name="status_kwt_his" id="status_kwt_his" class="form-control" required>
                                    <option value="belum">Belum Kwitansi</option>
                                    <option value="sudah">Sudah Kwitansi</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" class="form-control" value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    Export
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="tableKwitansi" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No. KWT</th>
                            <th>Tanggal</th>
                            <th>Invoice</th>
                            <th>Customer / Keterangan</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                </table>
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
    let table = $('#tableKwitansi').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('kwitansi.data') }}",
            data: function(d) {
                d.status_kwt = $('#status_kwt_his').val();
            }
        },
       columns: [
            { data: 'DT_RowIndex', searchable: false, orderable: false },
            { data: 'NOKWT' },
            { data: 'INVOICE' },
            { data: 'TGLINVOICE' },
            { data: 'KETERANGAN' },
            { data: 'NILAI', className: 'text-end' },
        ]
    });

    $('#status_kwt_his').change(function() {
        table.ajax.reload();
    });
});
</script>
