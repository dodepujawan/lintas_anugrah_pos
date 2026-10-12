@extends('dashboard.index')
@section('content')
<div class="main-master">
    <div class="master-page">
        {{-- <h1>Main Page</h1>
        <h2>Lintas Anugrah</h2>
        <div>Expedisi</div> --}}
    </div>
</div>

{{-- Download Loading Modal --}}
<div class="modal fade" id="loading_modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <!-- Gambar loading -->
                {{-- <img src="{{ asset('assets/gambar/loading3.gif') }}" alt="Loading..." class="img-fluid mb-3" style="width: 80px; height: 80px;"> --}}
                <h5 class="fw-bold text-dark mb-2">Memproses Data...</h5>
                <p class="text-muted small mb-0">Harap tunggu sebentar</p>
                <div class="spinner-border text-primary mt-3" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- End Of Download Loading Modal --}}
{{-- Modal Update Pajak --}}
<div class="modal fade" id="pajakModal" tabindex="-1" aria-labelledby="pajakModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="pajakForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="pajakModalLabel">Update Pajak</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal-ppn" class="form-label">Nilai PPN (%)</label>
                        <input type="number" step="0.01" class="form-control" id="modal-ppn" name="ppn" required>
                        <div class="form-text">Masukkan nilai PPN dalam persen (contoh: 11.0 untuk 11%)</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="id" id="modal-id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit_pajak">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- End Of Modal Update Pajak --}}
{{-- Modal Update Signature --}}
<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="signatureForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="signatureModalLabel">Update Signature</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal-signature" class="form-label">Nama</label>
                        <input type="text" step="0.01" class="form-control" id="modal-signature" name="signature" required>
                        <div class="form-text">Masukan Nama Pennaggung Jawab</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="id" id="modal-id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit_signature">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- End Of Modal Update Signature --}}
{{-- Modal Printer --}}
<div class="modal fade" id="printerModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pilih Printer</h5>
      </div>
      <div class="modal-body">
        <select id="printerSelect" class="form-select"></select>
        <hr>
        <small class="text-muted">
        Jika printer tidak muncul, install JSPrintManager terlebih dahulu.
        </small>
      </div>
      <div class="modal-footer">
        <a href="{{ asset('assets/print/PrintService Setup 1.0.0.exe') }}"
           class="btn btn-warning"
           download>
           Download Print Manager
        </a>
        <button class="btn btn-primary" id="savePrinter">
            Simpan
        </button>
      </div>
    </div>
  </div>
</div>
{{-- End Of Modal Printer --}}
{{-- Modal Area --}}
<!-- ===================== MODAL AREA ===================== -->
<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="areaForm">
            <div class="modal-content">
                <!-- HEADER -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="areaModalLabel">Master Area</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <!-- BODY -->
                <div class="modal-body">
                    <!-- AREA -->
                    <div class="mb-3">
                        <label class="form-label">Nama Area</label>
                        <input type="text" class="form-control" id="modal-area" name="area" placeholder="Contoh : Denpasar">
                        <div class="form-text">
                            Nama cabang / area
                        </div>
                    </div>

                    <!-- ALAMAT -->
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" id="modal-alamat" name="alamat" rows="3" placeholder="Masukan alamat area"></textarea>
                        <div class="form-text">
                            Alamat lengkap cabang
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_area">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- End Of Modal Area --}}
@endsection
@section('footer')
<script>
$(document).ready(function() {
    // ### TAMPILKAN DASHBOARD ###
    loadDashboard();
// ########################### NAVBAR ROOM ##############################################
// ========================= Edit Profile ======================================
    $(document).on('click', '#navbar_edit_profile', function(e) {
        e.preventDefault();
        loadEditProfileForm();
    });

    function loadEditProfileForm() {
        $.ajax({
            url: '{{ route('editregister') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of Edit Profile ======================================
// ========================= Register Customer ======================================
    $(document).on('click', '#sidebar_register_customer', function(e) {
        e.preventDefault();
        loadRegisterCustomerForm();
    });

    function loadRegisterCustomerForm() {
        $.ajax({
            url: '{{ route('index_customer') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of Register Customer ======================================
// ####################### End Of NAVBAR ROOM ###########################################

// +++++++++++++++++++++++++++++++ SIDEBAR ROOM +++++++++++++++++++++++++++++++++++++++++
// ========================= Dashboard ======================================
    $(document).on('click', '#sidebar_dashboard', function(e) {
        e.preventDefault();
        loadDashboard();
    });

    function loadDashboard() {
        $.ajax({
            url: '{{ route('dashboard.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of Dashboard ======================================
// ========================= List Register ======================================
    $(document).on('click', '#sidebar_list_user', function(e) {
        e.preventDefault();
        loadListRegisterForm();
    });

    function loadListRegisterForm() {
        $.ajax({
            url: '{{ route('listregister') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of List Register ======================================
// ========================= New Register ======================================
    $(document).on('click', '#sidebar_new_user', function(e) {
        e.preventDefault();
        loadNewRegisterForm();
    });

    function loadNewRegisterForm() {
        $.ajax({
            url: '{{ route('register') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Register ======================================
// ========================= New Kendaraan ======================================
    $(document).on('click', '#sidebar_new_kendaraan', function(e) {
        e.preventDefault();
        loadNewKendaraanForm();
    });

    function loadNewKendaraanForm() {
        $.ajax({
            url: '{{ route('kendaraan.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Kendaraan ======================================
// ========================= New Driver ======================================
    $(document).on('click', '#sidebar_new_driver', function(e) {
        e.preventDefault();
        loadNewDriverForm();
    });

    function loadNewDriverForm() {
        $.ajax({
            url: '{{ route('driver.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Driver ======================================
// ========================= New Prices ======================================
    $(document).on('click', '#sidebar_new_prices', function(e) {
        e.preventDefault();
        loadNewPriceForm();
    });

    function loadNewPriceForm() {
        $.ajax({
            url: '{{ route('price-expedition.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Prices ======================================
// ========================= New Prices Customer ======================================
    $(document).on('click', '#sidebar_prices_customer', function(e) {
        e.preventDefault();
        loadPriceCustomerForm();
    });

    function loadPriceCustomerForm() {
        $.ajax({
            url: '{{ route('price-customer.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Prices Customer ======================================
// ========================= New Price Dingin ======================================
    $(document).on('click', '#sidebar_new_rent', function(e) {
        e.preventDefault();
        loadNewRentForm();
    });

    function loadNewRentForm() {
        $.ajax({
            url: '{{ route('price-rent.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Price Dingin ======================================
// ========================= New Price Dingin ======================================
    $(document).on('click', '#sidebar_rent_customer', function(e) {
        e.preventDefault();
        loadNewRentCusForm();
    });

    function loadNewRentCusForm() {
        $.ajax({
            url: '{{ route('price-rentcus.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Price Dingin ======================================
// ========================= New Expedisi ======================================
    $(document).on('click', '#sidebar_new_expedisi', function(e) {
        e.preventDefault();
        loadNewExpedisiForm();
    });

    function loadNewExpedisiForm() {
        $.ajax({
            url: '{{ route('expedisi.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Expedisi ======================================
// ========================= New Expedisi Invoice ======================================
    $(document).on('click', '#sidebar_invoice_expedisi', function(e) {
        e.preventDefault();
        loadInvoiceExpedisiForm();
    });

    function loadInvoiceExpedisiForm() {
        $.ajax({
            url: '{{ route('expedisiInvoice.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ======================= End Of New Expedisi Invoice ====================================
// ========================= New Expedisi Invoice Generate ======================================
    $(document).on('click', '#sidebar_Invoice_gen_expedisi', function(e) {
        e.preventDefault();
        loadInvoiceExpedisiGenForm();
    });

    function loadInvoiceExpedisiGenForm() {
        $.ajax({
            url: '{{ route('expedisiInvoiceGenerate.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ======================= End Of New Expedisi Invoice Generate ====================================
// ========================= New Expedisi Invoice Kwitansi ======================================
    $(document).on('click', '#sidebar_Invoice_kwt_expedisi', function(e) {
        e.preventDefault();
        loadInvoiceExpedisiKwtForm();
    });

    function loadInvoiceExpedisiKwtForm() {
        $.ajax({
            url: '{{ route('expedisiKwitansi.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ======================= End Of New Expedisi Invoice Kwitansi ====================================
// ========================= New Expedisi Invoice Edit ======================================
    $(document).on('click', '#sidebar_Invoice_edit_expedisi', function(e) {
        e.preventDefault();
        loadInvoiceExpedisiEditForm();
    });

    function loadInvoiceExpedisiEditForm() {
        $.ajax({
            url: '{{ route('expedisiInvoiceEdit.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ======================= End Of New Expedisi Invoice Edit ====================================
// ========================= New Rent Dingin ======================================
    $(document).on('click', '#sidebar_new_rent_dingin', function(e) {
        e.preventDefault();
        loadNewRentDPendinginForm();
    });

    function loadNewRentDPendinginForm() {
        $.ajax({
            url: '{{ route('rentPendingin.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of New Rent Dingin ======================================
// ========================= Invoice Rent Dingin ======================================
    // $(document).on('click', '#sidebar_invoice_rent_dingin', function(e) {
    //     e.preventDefault();
    //     loadInvPendinginForm();
    // });

    // function loadInvPendinginForm() {
    //     $.ajax({
    //         url: '{{ route('rentPendinginInv.index') }}', // Route to load the form
    //         type: 'GET',
    //         success: function(response) {
    //             $('.master-page').html(response);
    //         },
    //         error: function() {
    //             $('.master-page').html('<p>Error loading form.</p>');
    //         }
    //     });
    // }
// ========================= End Of Invoice Rent Dingin ======================================
// ========================= Generate Invoice Rent Dingin ======================================
    $(document).on('click', '#sidebar_generate_rent_dingin', function(e) {
        e.preventDefault();
        loadGenPendinginForm();
    });

    function loadGenPendinginForm() {
        $.ajax({
            url: '{{ route('rentPendinginInvGen.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of Generate Invoice Rent Dingin =================================
// ========================= Kwitansi Rent Dingin ======================================
    $(document).on('click', '#sidebar_generate_kwt_dingin', function(e) {
        e.preventDefault();
        loadKwtPendinginForm();
    });

    function loadKwtPendinginForm() {
        $.ajax({
            url: '{{ route('pendinginKwitansi.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ========================= End Of Kwitansi Rent Dingin ======================================
// ========================= New Rent Dingin Invoice Edit ======================================
    $(document).on('click', '#sidebar_Invoice_edit_rent', function(e) {
        e.preventDefault();
        loadInvoiceRentEditForm();
    });

    function loadInvoiceRentEditForm() {
        $.ajax({
            url: '{{ route('rentPendinginInvGen.indexEdit') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ======================= End Of New Rent Dingin Invoice Edit ====================================
// ================================== Coolroom ======================================
    $(document).on('click', '#sidebar_coolroom', function(e) {
        e.preventDefault();
        loadCoolRoomForm();
    });

    function loadCoolRoomForm() {
        $.ajax({
            url: '{{ route('coolroom.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// =================================== End Of Coolroom ======================================
// ================================== Coolroom Invoice ======================================
    $(document).on('click', '#sidebar_coolroom_inv', function(e) {
        e.preventDefault();
        loadCoolRoomInvForm();
    });

    function loadCoolRoomInvForm() {
        $.ajax({
            url: '{{ route('coolroomInv.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ================================= End Of CoolroomInvoice ====================================
// ================================== Coolroom Kiwitansi ======================================
    $(document).on('click', '#sidebar_coolroom_kwt', function(e) {
        e.preventDefault();
        loadCoolRoomKwtForm();
    });

    function loadCoolRoomKwtForm() {
        $.ajax({
            url: '{{ route('coolroomKwt.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ================================= End Of Coolroom Kwitansi ====================================
// ========================= Coolroom Invoice Edit ======================================
    $(document).on('click', '#sidebar_coolroom_edit', function(e) {
        e.preventDefault();
        loadInvoiceCoolroomEditForm();
    });

    function loadInvoiceCoolroomEditForm() {
        $.ajax({
            url: '{{ route('coolroomInv.indexEdit') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ======================= End Of Coolroom Invoice Edit ====================================
// ========================= Kwitansi History ======================================
    $(document).on('click', '#sidebar_new_kwitansi', function(e) {
        e.preventDefault();
        loadKwitansiHisForm();
    });

    function loadKwitansiHisForm() {
        $.ajax({
            url: '{{ route('index.kwitansi') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ======================= End Of Kwitansi History ====================================
// ================================ Form Supplier ======================================
    $(document).on('click', '#sidebar_new_supplier', function(e) {
        e.preventDefault();
        loadSupplierForm();
    });

    function loadSupplierForm() {
        $.ajax({
            url: '{{ route('msupplier.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ============================ End Of Form Supplier ====================================
// ================================ Form Service ======================================
    $(document).on('click', '#sidebar_new_service', function(e) {
        e.preventDefault();
        loadServiceForm();
    });

    function loadServiceForm() {
        $.ajax({
            url: '{{ route('service.index') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('.master-page').html(response);
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    }
// ============================ End Of Form Service ====================================
// ========================= Update Pajak ======================================
    $(document).on('click', '#sidebar_extra_pajak', function(e) {
        e.preventDefault();
        loadDataPajak();
        function loadDataPajak(){
            $.ajax({
                url: '{{ route('get_pajak') }}',
                type: 'GET',
                success: function(response) {
                    let nilai_ppn = response.data.ppn;
                    $('#modal-ppn').val(nilai_ppn);
                },
                error: function() {
                    $('#modal-ppn').val('Error Loading');
                }
            });
        }
        $('#pajakModal').modal('show');
    });
    // ### Submit Pajak ###
    $('#submit_pajak').on('click', function (e) {
    e.preventDefault();
    let ppn_pajak = $('#modal-ppn').val();
        $.ajax({
            url: '{{ route('update_pajak') }}', // Ganti sesuai route di Laravel kamu
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                ppn: ppn_pajak
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message || 'PPN berhasil disimpan!',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#pajakModal').modal('hide');
            },
            error: function (xhr) {
                console.error('Gagal:', xhr.responseText);
                alert('Gagal menyimpan PPN');
            }
        });
    });
// ========================= End Of Update Pajak ======================================
// =============================== Rekening ========================================
    $(document).on('click', '#sidebar_extra_rekening', function(e) {
        e.preventDefault();
        loadDataRekening();
        function loadDataRekening(){
            $.ajax({
                url: '{{ route('rekening.index') }}',
                type: 'GET',
                success: function(response) {
                    $('.master-page').html(response);
                },
                error: function() {
                   $('.master-page').html('<p>Error loading form.</p>');
                }
            });
        }
    });
// ============================== End Of Rekening =======================================
// ========================= Update Signature ======================================
    $(document).on('click', '#sidebar_extra_signature', function(e) {
        e.preventDefault();
        loadDataSign();
        function loadDataSign(){
            $.ajax({
                url: '{{ route('get_signature') }}',
                type: 'GET',
                success: function(response) {
                    let nilai_signature = response.data.nama;
                    $('#modal-signature').val(nilai_signature);
                },
                error: function() {
                    $('#modal-signature').val('Error Loading');
                }
            });
        }
        $('#signatureModal').modal('show');
    });
    // ### Submit Signature ###
    $('#submit_signature').on('click', function (e) {
    e.preventDefault();
    let nama_signature = $('#modal-signature').val();
        $.ajax({
            url: '{{ route('update_signature') }}', // Ganti sesuai route di Laravel kamu
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                signature: nama_signature
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message || 'Signature berhasil disimpan!',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#signatureModal').modal('hide');
            },
            error: function (xhr) {
                console.error('Gagal:', xhr.responseText);
                alert('Gagal menyimpan PPN');
            }
        });
    });
// ========================= End Of Update Signature ======================================
// ==================================== Printer ========================================

    // ### Ambil Printer Dari JSPrint Select
    $('#sidebar_extra_printer').click(function(e){
        e.preventDefault();
        loadPrinters();
        $('#printerModal').modal('show');
    });
    // ### Ambil Printer Dari JSPrint Select
    function loadPrinters(){
        fetch('http://localhost:3000/printers')
        .then(res => res.json())
        .then(printers => {
            let select = $('#printerSelect');
            select.empty();
            printers.forEach(function(printer){
                select.append(
                    `<option value="${printer}">${printer}</option>`
                );
                // console.log('nig: ' + printer);
            });
            // 🔥 ambil dari database (TETAP SAMA)
            $.get("{{ route('printer.current') }}", function(res){
                if(res.printer){
                    select.val(res.printer);
                }
            });
        })
        .catch(err => {
            alert("Print service tidak aktif");
            console.log(err);
        });
    }
    // ### Save Printer
    $('#savePrinter').click(function(){
        let printer = $('#printerSelect').val();
        $.ajax({
            url: "{{ route('printer.save') }}",
            method:'POST',
            data:{
                printer:printer,
                _token:'{{ csrf_token() }}'
            },
            success:function(res){
                if(res.status){
                    alert('Printer disimpan');
                    $('#printerModal').modal('hide');
                }
            }
        });
    });
// =============================== End Of Printer ======================================
// =============================== Hak Akses ========================================
    $(document).on('click', '#sidebar_extra_permissions', function(e) {
        e.preventDefault();
        loadDataPermissions();
        function loadDataPermissions(){
            $.ajax({
                url: '{{ route('index.permissions') }}',
                type: 'GET',
                success: function(response) {
                    $('.master-page').html(response);
                },
                error: function() {
                   $('.master-page').html('<p>Error loading form.</p>');
                }
            });
        }
    });
// ============================== End Of Hak Akses =======================================
// ==================================== Area ==========================================
    function loadAreaSelect(selectedId = '') {
        $.ajax({
            url: '{{ route('get_area') }}',
            type: 'GET',
            success: function(response) {
                let html = '<option value="">-- Pilih Area --</option>';
                $.each(response.data, function(i, item) {
                    let selected =
                        selectedId == item.id
                        ? 'selected'
                        : '';
                    html += `
                        <option value="${item.id}"
                                data-alamat="${item.alamat}"
                                ${selected}>
                            ${item.area}
                        </option>
                    `;
                });
                $('#select_area').html(html);
            }
        });
    }

    // ======================================
    // BUKA MODAL
    // ======================================
    $(document).on('click', '#sidebar_extra_area', function(e) {
        e.preventDefault();
        $('#modal-area').val('');
        $('#modal-alamat').val('');
        $('#areaModal').modal('show');
    });

    // Auto Capital
    $('#modal-area').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // ======================================
    // SUBMIT AREA
    // ======================================

    $('#areaForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('store_area') }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                area: $('#modal-area').val(),
                alamat: $('#modal-alamat').val(),
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#areaModal').modal('hide');
                // reload select
                loadAreaSelect();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menyimpan area'
                });
            }
        });
    });

    // ======================================
    // AUTO TAMPILKAN ALAMAT
    // ======================================
    $(document).on('change', '#select_area', function() {
        let alamat =
            $(this)
            .find(':selected')
            .data('alamat');
        $('#alamat_area').val(alamat ?? '');
    });

    // ======================================
    // AUTO LOAD PAS HALAMAN DIBUKA
    // ======================================

    loadAreaSelect();
// =============================== End Of Area ========================================
// +++++++++++++++++++++++++++ End Of SIDEBAR ROOM ++++++++++++++++++++++++++++++++++++++

    // ===================== ### SPA HISTORY (SIDEBAR) ### =====================
    // Add IDs for new AJAX sidebar pages to pageIds below.
    const pageIds = new Set([
        'sidebar_dashboard',
        'sidebar_register_customer',
        'sidebar_list_user',
        'sidebar_new_user',
        'sidebar_new_kendaraan',
        'sidebar_new_driver',
        'sidebar_new_prices',
        'sidebar_prices_customer',
        'sidebar_new_rent',
        'sidebar_rent_customer',
        'sidebar_new_expedisi',
        'sidebar_invoice_expedisi',
        'sidebar_Invoice_gen_expedisi',
        'sidebar_Invoice_kwt_expedisi',
        'sidebar_Invoice_edit_expedisi',
        'sidebar_new_rent_dingin',
        'sidebar_generate_rent_dingin',
        'sidebar_generate_kwt_dingin',
        'sidebar_Invoice_edit_rent',
        'sidebar_coolroom',
        'sidebar_coolroom_inv',
        'sidebar_coolroom_kwt',
        'sidebar_coolroom_edit',
        'sidebar_new_kwitansi',
        'sidebar_new_supplier',
        'sidebar_new_service',
        'sidebar_extra_rekening',
        'sidebar_extra_permissions'
    ]);

    const historyStateKey = '__laSpaPage';
    let restoringFromHistory = false;

    // The initial /login/home view is loaded by loadDashboard() above.
    const initialHistoryState = Object.assign({}, window.history.state || {}, {
        [historyStateKey]: 'sidebar_dashboard'
    });
    window.history.replaceState(initialHistoryState, '', window.location.href);

    // Track page links without replacing their existing click/AJAX handlers.
    $(document).on(
        'click.spaHistory',
        '#sneatSidebar .menu-link[id^="sidebar_"]',
        function() {
            const pageId = this.id;

            if (restoringFromHistory || !pageIds.has(pageId)) return;
            if (window.history.state && window.history.state[historyStateKey] === pageId) return;

            const nextState = Object.assign({}, window.history.state || {}, {
                [historyStateKey]: pageId
            });
            const nextUrl = new URL(window.location.href);
            nextUrl.hash = 'spa=' + encodeURIComponent(pageId);

            window.history.pushState(nextState, '', nextUrl.toString());
        }
    );

    // Reuse the existing sidebar click handler when Back/Forward is pressed.
    window.addEventListener('popstate', function(event) {
        const pageId = event.state && event.state[historyStateKey];
        if (!pageId || !pageIds.has(pageId)) return;

        const link = document.getElementById(pageId);
        if (!link) return;

        restoringFromHistory = true;
        try {
            $(link).trigger('click');
        } finally {
            restoringFromHistory = false;
        }
    });
    // =================== ### END SPA HISTORY (SIDEBAR) ### ===================
});
</script>
@endsection
