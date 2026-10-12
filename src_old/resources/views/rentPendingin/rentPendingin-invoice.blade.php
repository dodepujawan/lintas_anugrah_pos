<div class="container-fluid mt-3">
    <!-- Header Form -->
    <div class="card-rent_pendingin" id="master_form_dgn_inv">
        <div class="card-rent_pendingin-header">
            <h5><i class='bx bx-truck me-2'></i>INVOICE MOBIL PENDINGIN</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label d-block">Filter Invoice</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input"
                            type="radio" name="filter_invoice_dgn" id="radio_belum_dgn" value="belum" checked>
                        <label class="form-check-label" for="radio_belum_dgn">
                            Belum Invoice
                        </label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="filter_invoice_dgn" id="radio_sudah_dgn" value="sudah">
                        <label class="form-check-label" for="radio_sudah_dgn">
                            Sudah Invoice
                        </label>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Tanggal Mulai</label>
                    <input type="date" class="form-control form-control-sm" id="filter_tgl_mulai">
                </div>
                <div class="col-md-3">
                    <label>Tanggal Akhir</label>
                    <input type="date" class="form-control form-control-sm" id="filter_tgl_akhir">
                </div>
                <div class="col-md-3">
                    <label>Filter Data</label>
                    <input type="text" class="form-control form-control-sm" id="filter_invoice_rent_dgn">
                </div>
                <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button class="btn btn-sm btn-info" id="btn_filter_invoice_rent_dgn">
                                <i class='bx bx-filter'></i> Filter
                            </button>
                        </div>
                    </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="InvoiceDgnTable">
                    <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>NO MUAT</th>
                        <th>TGL MUAT</th>
                        <th>CUSTOMER</th>
                        <th>PESANAN AWAL</th>
                        <th>KENDARAAN</th>
                        <th>JUMLAH</th>
                        <th>HARGA</th>
                        <th>DISC</th>
                        <th>DEL CHARGE</th>
                        <th>TOTAL</th>
                        <th>NO SJ</th>
                        <th>NO INVOICE</th>
                        <th>PROSES</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Bagian Proses --}}
    <div class="d-none mt-4" id="form_gabung_inv_exp">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold">
                <i class='bx bx-receipt me-2'></i> DATA MUAT / INVOICE
            </div>

            <div class="card-body">

                <!-- INFORMASI MUAT -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label small">Nomor Invoice</label>
                        <input type="text" class="form-control form-control-sm" name="no_invoice_dgn_inv" id="no_invoice_dgn_inv" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Nomor Muat</label>
                        <input type="text" class="form-control form-control-sm"
                            id="no_gabung_dgn_inv" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Customer</label>
                        <input type="hidden" id="customer_kode_gabung_dgn_inv">
                        <input type="text" class="form-control form-control-sm"
                            id="customer_gabung_dgn_inv" readonly>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label small">No. SJ</label>
                        <input type="text" class="form-control form-control-sm" id="surjal_gabung_dgn_inv">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small">Item</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control"
                                id="item_gabung_dgn_inv" readonly>
                            {{-- <button class="btn btn-outline-primary"
                                type="button" id="btnItemGabungDgn">
                                <i class="bx bx-search"></i>
                            </button> --}}
                        </div>
                    </div>
                </div>

                <hr>

                <!-- PERHITUNGAN -->
                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label small">Jumlah (KG)</label>
                        <input type="text"
                            class="form-control form-control-sm text-end"
                            id="jumlah_gabung_dgn_inv">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Harga @</label>
                        <input type="text"
                            class="form-control form-control-sm text-end"
                            id="harga_gabung_dgn_inv">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Diskon %</label>
                        <input type="text"
                            class="form-control form-control-sm text-end"
                            id="diskon_gabung_dgn_inv">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Del Charge</label>
                        <input type="text"
                            class="form-control form-control-sm text-end"
                            id="dc_gabung_dgn_inv">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">PPN %</label>
                        <input type="text"
                            class="form-control form-control-sm text-end"
                            id="ppn_gabung_dgn_inv">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">DPP</label>
                        <input type="text"
                            class="form-control form-control-sm text-end fw-bold bg-light"
                            id="dpp_gabung_dgn_inv" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Sub Total</label>
                        <input type="text"
                            class="form-control form-control-sm text-end fw-bold bg-light"
                            id="total_gabung_dgn_inv" readonly>
                    </div>

                </div>

                <div class="row mt-4">
                    <div class="col-md-4 ms-auto">
                        <label class="form-label fw-bold">Grand Total</label>
                        <input type="text"
                            class="form-control form-control-sm text-end fw-bold bg-warning-subtle"
                            id="grand_total_gabung_dgn_inv"
                            readonly>
                    </div>
                </div>

                <input type="hidden" id="jenis_hrg_dgn_inv">

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-success px-4"
                    id="gabungInvDgnBtnLeft">
                    <i class="bx bx-check-circle me-1"></i>
                    Proses Invoice
                </button>
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
    // ================================= Tabel No Muat =====================================
    // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#InvoiceDgnTable')) {
            $('#InvoiceDgnTable').DataTable().destroy();
        }
        // untuk kepentingan ambil nilai CustomerKode saat di highlights
        let selectedRowData = null;
        var tableInvoiceDgn = $('#InvoiceDgnTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
            url: "{{ route('rentPendinginInv.data') }}",
                data: function(d) {
                    d.tgl_mulai = $('#filter_tgl_mulai').val();
                    d.tgl_akhir = $('#filter_tgl_akhir').val();
                    d.search_muat = $('#filter_invoice_rent_dgn').val();
                    d.filter_invoice = $('input[name="filter_invoice_dgn"]:checked').val();
                }
            },
            // Scroll settings
            scrollX: true,
            scrollY: "400px",
            scrollCollapse: true,
            // Responsive settings
            responsive: true,
            autoWidth: true,
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'NOMUAT' },
                { data: 'TGLMUAT' },
                { data: 'CUSTOMER' },
                { data: 'PESANAN' },
                { data: 'NAMA_KENDARAAN' },
                { data: 'JUMLAH' },
                { data: 'harga_formatted' },
                { data: 'DISC' },
                { data: 'dc_formatted' },
                { data: 'total_formatted' },
                { data: 'NOSJ' },
                { data: 'INVOICE' },
                { data: 'aksi', orderable: false, searchable: false }
            ]
        });

        $('#btn_filter_invoice_rent_dgn').click(function () {
            tableInvoiceDgn.ajax.reload();
        });

        $('input[name="filter_invoice_dgn"]').change(function () {
            tableInvoiceDgn.ajax.reload();
        });
    // ============================= End Of Tabel No Muat =====================================
    // ============================= End Of Pilih No Muat =====================================
    $('#InvoiceDgnTable').on('click', '.btn-detail-dgn-inv', function () {
        let nomuat = $(this).data('nomuat');
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('rentPendinginInv.detail') }}",
                type: "GET",
                data: { nomuat: nomuat },
                success: function(res) {

                    if(res.status){
                        $('#loading_modal').modal('hide');
                        let d = res.data;
                        $('#master_form_dgn_inv').addClass('d-none');
                        $('#form_gabung_inv_exp').removeClass('d-none');

                        $('#no_invoice_dgn_inv').val(d.INVOICE || "");
                        $('#no_gabung_dgn_inv').val(d.NOMUAT);
                        $('#customer_kode_gabung_dgn_inv').val(d.CUSTOMER_KODE);
                        $('#customer_gabung_dgn_inv').val(d.CUSTOMER);
                        $('#surjal_gabung_dgn_inv').val(d.NOSJ);
                        $('#item_gabung_dgn_inv').val(d.PESANAN);
                        $('#jumlah_gabung_dgn_inv').val(parseFloat(d.JUMLAH) || 0);
                        $('#harga_gabung_dgn_inv').val(parseFloat(d.HARGA) || 0);
                        $('#diskon_gabung_dgn_inv').val(parseFloat(d.DISC) || 0);
                        $('#dc_gabung_dgn_inv').val(parseFloat(d.DC) || 0);
                        $('#ppn_gabung_dgn_inv').val(parseFloat(d.PPN));
                        $('#dpp_gabung_dgn_inv').val(formatIDR(toNumber(d.TOTAL)));
                        $('#total_gabung_dgn_inv').val(formatIDR(toNumber(d.TOTAL)));
                        $('#grand_total_gabung_dgn_inv').val(formatIDR(toNumber(d.GRAND)));
                        $('#jenis_hrg_dgn_inv').val(d.JENISHRG);

                    }
                }
            });
        });
    });
    // ============================= End Of Pilih No Muat =====================================
    // ================================= Keyups Hitung =======================================
    $('#jumlah_gabung_dgn_inv, #harga_gabung_dgn_inv, #diskon_gabung_dgn_inv, #ppn_gabung_dgn_inv, #dc_gabung_dgn_inv').on('keyup change', function () {
        hitungTotalDgnInv();
    });
    // ============================== End Of Keyups Hitung ====================================
    // ================================= Submit No Muat =======================================
    $('#gabungInvDgnBtnLeft').on('click', function () {
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            let url = $('#no_invoice_dgn_inv').val() ? "{{ route('rentPendinginInv.update') }}" : "{{ route('rentPendinginInv.store') }}";
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    noinvoice: $('#no_invoice_dgn_inv').val(),
                    nomuat: $('#no_gabung_dgn_inv').val(),
                    item: $('#item_gabung_dgn_inv').val(),

                    harga: toNumber($('#harga_gabung_dgn_inv').val()),
                    diskon: toNumber($('#diskon_gabung_dgn_inv').val()),
                    dc: toNumber($('#dc_gabung_dgn_inv').val()),
                    total: toNumber($('#total_gabung_dgn_inv').val()),
                    ppn: toNumber($('#ppn_gabung_dgn_inv').val()),
                    grand_total: toNumber($('#grand_total_gabung_dgn_inv').val())
                },

                success: function (res) {

                    if (res.status) {
                        $('#loading_modal').modal('hide');
                        Swal.fire('Success', res.message, 'success');

                        // redirect ke print
                        window.open(res.redirect, '_blank');

                        // reset form
                        clearFormDgnInv();
                        $('#form_gabung_inv_exp').addClass('d-none');
                        $('#master_form_dgn_inv').removeClass('d-none');
                        // reload table
                        $('#InvoiceDgnTable').DataTable().clear().draw();
                        $('#InvoiceDgnTable').DataTable().ajax.reload();

                    } else {
                        $('#loading_modal').modal('hide');
                        Swal.fire('Error', res.message, 'error');
                    }
                },

                error: function (xhr) {
                    $('#loading_modal').modal('hide');
                    let msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan';
                    // alert(msg);
                },
            });
        });
    });
    // ============================= End Of Submit No Muat =====================================
});
    // ########################################################################
    // FUNCTION HELPER:
    // ########################################################################

    // Fungsi Clear Form Invoice
    function clearFormDgnInv() {
        // Text biasa
        $('#no_gabung_dgn_inv').val('');
        $('#customer_kode_gabung_dgn_inv').val('');
        $('#customer_gabung_dgn_inv').val('');
        $('#surjal_gabung_dgn_inv').val('');
        $('#item_gabung_dgn_inv').val('');
        $('#jenis_hrg_dgn_inv').val('');

        // Numeric reset ke 0
        $('#jumlah_gabung_dgn_inv').val(0);
        $('#harga_gabung_dgn_inv').val(0);
        $('#diskon_gabung_dgn_inv').val(0);
        $('#dc_gabung_dgn_inv').val(0);
        $('#ppn_gabung_dgn_inv').val(0);

        // Total format reset
        $('#dpp_gabung_dgn_inv').val('0');
        $('#total_gabung_dgn_inv').val('0');
        $('#grand_total_gabung_dgn_inv').val('0');
    }

    // Fungsi clean number
    function toNumber(val) {
        if (!val) return 0;
        return parseFloat(
            val.toString()
            .replace(/\./g, '')   // hapus ribuan
            .replace(',', '.')    // jaga-jaga desimal
        ) || 0;
    }

    // Fungsi Untuk Ubah Ke Rupiah
    function formatIDR(num) {
        return num.toLocaleString('id-ID');
    }

    // Fungsi Penjumlahan Grand Total
    function hitungTotalDgnInv() {
        let jumlah = parseFloat($('#jumlah_gabung_dgn_inv').val()) || 0;
        let harga = parseFloat($('#harga_gabung_dgn_inv').val()) || 0;
        let diskonPersen = parseFloat($('#diskon_gabung_dgn_inv').val()) || 0;
        let ppnPersen = parseFloat($('#ppn_gabung_dgn_inv').val()) || 0;
        let dc = parseFloat($('#dc_gabung_dgn_inv').val()) || 0;

        // 1️⃣ Subtotal
        let subtotal = jumlah * harga;

        // 2️⃣ Diskon Rupiah
        let diskonRupiah = subtotal * (diskonPersen / 100);

        // 3️⃣ DPP (setelah diskon)
        let dpp = subtotal + dc - diskonRupiah;

        // 4️⃣ PPN Rupiah
        let ppnRupiah = dpp * (ppnPersen / 100);

        // 5️⃣ Grand Total
        let grandTotal = dpp + ppnRupiah + dc;

        // Isi ke form
        $('#dpp_gabung_dgn_inv').val(formatIDR(toNumber(dpp.toFixed(0))));
        $('#total_gabung_dgn_inv').val(formatIDR(toNumber(dpp.toFixed(0))));
        $('#grand_total_gabung_dgn_inv').val(formatIDR(toNumber(grandTotal.toFixed(0))));
}
</script>
