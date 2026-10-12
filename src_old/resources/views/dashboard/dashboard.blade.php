<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-md-4">
                <h3 class="fw-bold mb-0">
                    PT LINTAS ANUGRAH
                </h3>

                <small class="text-muted">
                    Dashboard Operasional
                </small>
            </div>

            <div class="col-md-8">
                <div class="row">

                    <div class="col-md-4">
                        <input
                            type="date"
                            id="tanggal_dari"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <input
                            type="date"
                            id="tanggal_sampai"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <button
                            id="btnFilterDashboard"
                            class="btn btn-primary w-100">
                            Filter Data
                        </button>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>
<div class="row">

    <!-- EKS -->
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h6 class="text-muted mb-2">
                            Expedisi EKS
                        </h6>

                        <h2
                            id="eks_total"
                            class="fw-bold mb-1">
                            0
                        </h2>

                        <small class="text-muted">
                            Transaksi
                        </small>

                    </div>

                    <div class="text-end">

                        <h5
                            id="eks_grand"
                            class="text-primary fw-bold">
                            Rp 0
                        </h5>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- REN -->
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h6 class="text-muted mb-2">
                            Sewa Mobil
                        </h6>

                        <h2
                            id="ren_total"
                            class="fw-bold mb-1">
                            0
                        </h2>

                        <small class="text-muted">
                            Transaksi
                        </small>

                    </div>

                    <div class="text-end">

                        <h5
                            id="ren_grand"
                            class="text-primary fw-bold">
                            Rp 0
                        </h5>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- COOLROOM -->
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h6 class="text-muted mb-2">
                            Coolroom
                        </h6>

                        <h2
                            id="coolroom_total"
                            class="fw-bold mb-1">
                            0
                        </h2>

                        <small class="text-muted">
                            Transaksi
                        </small>

                    </div>

                    <div class="text-end">

                        <h5
                            id="coolroom_grand"
                            class="text-primary fw-bold">
                            Rp 0
                        </h5>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-4">

        <small class="text-muted">
            TOTAL GRAND
        </small>

        <h1
            id="total_grand"
            class="fw-bold text-success mb-0">
            Rp 0
        </h1>

    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            Grand Total 30 Hari Terakhir
        </h5>
    </div>

    <div class="card-body">
        <canvas
            id="grandChart"
            height="70">
        </canvas>
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
    // Panggil Tampilan Card
    loadSummary();
    loadChart();

    function formatRupiah(angka)
    {
        return new Intl.NumberFormat(
            'id-ID'
        ).format(angka);
    }

    function loadSummary()
    {
        $.ajax({
            url: "{{ route('dashboard.summary') }}",
            type: 'GET',
            data: {
                tanggal_dari: $('#tanggal_dari').val(),
                tanggal_sampai: $('#tanggal_sampai').val()
            },
            success: function(r)
            {
                $('#eks_total').text(r.eks.total);
                $('#eks_grand').text(
                    'Rp ' + formatRupiah(r.eks.grand)
                );

                $('#ren_total').text(r.ren.total);
                $('#ren_grand').text(
                    'Rp ' + formatRupiah(r.ren.grand)
                );

                $('#coolroom_total').text(r.coolroom.total);
                $('#coolroom_grand').text(
                    'Rp ' + formatRupiah(r.coolroom.grand)
                );

                $('#total_grand').text(
                    'Rp ' + formatRupiah(r.total_grand)
                );
            }
        });
    }

    $('#btnFilterDashboard').click(function () {
        loadSummary();
    });

    let grandChart; // Chart instance global
    function loadChart() {
        $.ajax({
            url: "{{ route('dashboard.chart') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                // Validasi response
                if (!response.labels || !response.data) {
                    console.error("Data chart tidak lengkap:", response);
                    return;
                }

                // Hancurkan chart lama jika ada
                if (grandChart) {
                    grandChart.destroy();
                }

                const ctx = document.getElementById('grandChart').getContext('2d');

                grandChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            label: 'Grand Total',
                            data: response.data,
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Gagal memuat data chart:", error);
                // Tampilkan pesan error ke user jika perlu
            }
        });
    }
});
</script>
