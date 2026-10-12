<div class="container mt-4">

    <div class="card mb-3">
        <div class="card-header">Tambah Rekening</div>
        <div class="card-body">

            <div class="row">
                <div class="col-md-4">
                    <input type="text" id="bank" class="form-control form-control-sm" placeholder="Nama Bank">
                </div>

                <div class="col-md-4">
                    <input type="text" id="norek" class="form-control form-control-sm" placeholder="No Rekening">
                </div>

                <div class="col-md-4">
                    <input type="text" id="nama" class="form-control form-control-sm" placeholder="Nama Pemilik">
                </div>
            </div>

            <button class="btn btn-sm btn-primary mt-2" id="btnSave">
                Simpan
            </button>

        </div>
    </div>


    <div class="card">
        <div class="card-header">Daftar Rekening</div>
        <div class="card-body">

            <table id="rekeningTable" class="table table-bordered table-sm w-100">
                <thead>
                    <tr>
                        <th>Bank</th>
                        <th>No Rek</th>
                        <th>Nama</th>
                        <th>User</th>
                        <th>Aktif</th>
                        <th>Aksi</th>
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

    let table = $('#rekeningTable').DataTable({
        ajax: {
            url: "{{ route('rekening.data') }}",
            dataSrc: ''
        },
        columns: [
            { data: 'BANK' },
            { data: 'NOREK' },
            { data: 'NAMA' },
            { data: 'USER' },
            {
                data: 'AKTIF',
                render: function(data){
                    return data == 1
                        ? '<span class="badge bg-success">Dipakai</span>'
                        : '-';
                }
            },
            {
                data: 'id',
                render: function(data){
                    return `
                        <button class="btn btn-sm btn-warning btnPilih"
                                data-id="${data}">
                            Pilih
                        </button>
                    `;
                }
            }
        ]
    });

    // SIMPAN
    $('#btnSave').click(function(){
        $.post("{{ route('rekening.store') }}",{
            _token: '{{ csrf_token() }}',
            bank: $('#bank').val(),
            norek: $('#norek').val(),
            nama: $('#nama').val()
        }, function(){
            table.ajax.reload();
        });
    });

    // PILIH
    $('#rekeningTable').on('click', '.btnPilih', function(){

        let id = $(this).data('id');
        let url = "{{ route('rekening.pilih', ':id') }}";
        url = url.replace(':id', id);

        $.post(url, {
            _token: '{{ csrf_token() }}'
        }, function(){
            table.ajax.reload();
        });
    });

});
</script>
