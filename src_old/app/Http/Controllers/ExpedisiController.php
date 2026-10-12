<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Expedisi;
use App\Models\Mcustomer;
use App\Models\Rute;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Carbon\Carbon;

class ExpedisiController extends Controller
{
    public function index()
    {
        return view('expedisi.expedisi');
    }

    public function getDataCustomer(){
        $customers = Mcustomer::select(['id', 'kode_cus','CUSTOMER', 'NAMACUST', 'TYPECUST', 'TELEPON', 'EMAIL', 'created_at']);

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function($customer) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-btn-customer-expedisi" data-id="'.$customer->kode_cus.'" data-name="'.$customer->NAMACUST.'" data-customer="'.$customer->CUSTOMER.'" data-bs-toggle="tooltip" title="Pilih pelanggan" aria-label="Pilih pelanggan">
                            <i class="bx bx-check"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeSurjal(Request $request){
        // Validasi
        $validator = Validator::make($request->all(), [
            'tglsj' => 'required|date',
            'CUSTOMER' => 'required|string|max:30',
            'rute' => 'required|string|max:30',
            'JUMLAH' => 'required|numeric|min:0',
            'HARGA' => 'required|numeric|min:0',
        ], [
            'TGLMUAT.required' => 'Tanggal surat jalan harus diisi',
            'CUSTOMER.required' => 'Customer harus dipilih',
            'rute.required' => 'Rute harus diisi',
            'JUMLAH.required' => 'Jumlah harus diisi',
            'HARGA.required' => 'Harga harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mulai transaction dengan isolasi tinggi
        DB::beginTransaction();

        try {
            // ======================
            // GENERATE NOMUAT DENGAN LOCK
            // ======================
            // $nomuat = $this->generateNomuatWithLock();
            $nosurjal = $this->generateNoSuratJalan();
            // $nojalan = $this->generateNoJalan();
            // ======================
            // HITUNG TOTAL DARI REQUEST
            // ======================
            $jumlah = $request->JUMLAH ?? 0;
            $harga = $request->HARGA ?? 0;
            $discPercent = $request->DISC ?? 0;
            $delCharge = $request->DC ?? 0;
            $ppnPercent = floatval($request->PPN);
            $subTotal = round($jumlah * $harga);
            $discAmount = round($subTotal * ($discPercent / 100));
            $totalAfterDisc = round($subTotal - $discAmount);
            $ppn = round($totalAfterDisc * ($ppnPercent / 100));
            $grandTotal = round($totalAfterDisc + $ppn + $delCharge);
            // ======================
            // PREPARE DATA
            // ======================
            $expedisiData = [
                // IDENTITAS & DOKUMEN
                // 'NOMUAT' => $nomuat,
                // 'TGLMUAT' => $request->TGLMUAT,
                // 'NOJALAN' => $nojalan,
                'tglsj' => $request->tglsj,
                'NOSJ' => $nosurjal,
                'WILAYAH' => $request->WILAYAH ?? 'denpasar',
                'CUSTOMER_KODE' => $request->customer_expedisi_id,
                'CUSTOMER' => $request->CUSTOMER,
                'PESANAN' => $request->item_expedisi,

                // KENDARAAN & DRIVER
                'KENDARAAN' => $request->kendaraan_expedisi_id,
                'NAMA_KENDARAAN' => $request->NAMA_KENDARAAN,
                'DRIVER' => $request->driver_1_expedisi_id,
                'NAMA_DRIVER' => $request->NAMA_DRIVER,
                'DRIVER2' => $request->driver_2_expedisi_id,
                'NAMA_DRIVER2' => $request->NAMA_DRIVER2,

                // PENERIMA
                'P_PENERIMA' => $request->P_PENERIMA,
                'P_NAMA' => $request->P_NAMA,
                'P_PHONE' => $request->P_PHONE,
                'P_ALAMAT' => $request->P_ALAMAT,

                // BARANG
                'barang' => $request->barang,
                'penyimpanan' => $request->penyimpanan,
                'koli' => $request->koli,
                'catatan' => $request->catatan,

                // DETAIL & PERHITUNGAN
                'rute' => $request->rute,
                'JUMLAH' => $jumlah,
                'UNIT' => $request->UNIT ?? 'KG',
                'HARGA' => $harga,
                'hargaaw' => $request->hargaaw ?? $harga,
                'DISC' => $discPercent,
                'DC' => $delCharge,
                'DCAW' => $request->DCAW ?? $delCharge,
                'NDISC' => $discAmount,
                'NDISCAW' => $discAmount,
                'TOTAL' => $totalAfterDisc,
                'PPN' => $request->PPN,
                'GRAND' => $grandTotal,

                // INVOICE
                // 'INVOICE' => $invoice,
                // 'TGLINVOICE' => $request->TGLMUAT,

                // STATUS & DEFAULT
                'JENISHRG' => $request->JENISHRG,
                'JENIS' => $request->JENIS ?? 'EKS',
                // 'STS' => $request->STS ?? 'INVOICE',
                'SIMPAN' => $request->SIMPAN ?? 'N',
                'READY' => $request->READY ?? 'Y',
                'CLOSSING' => $request->CLOSSING ?? 'N',
                'KETERANGAN' => $request->KETERANGAN ?? 'EXPEDISI BARU',

                // USER INFO
                'user_id' => auth()->user()->user_id ?? 'SYSTEM',
                'user' => auth()->user()->name ?? 'SYSTEM',
                // AREA
                'area_id' => auth()->user()->area_id,
                'area_name' => auth()->user()->area_name,
                // 'USERINV' => auth()->user()->name ?? 'SYSTEM',
                // 'USERKENDARAAN' => auth()->user()->name ?? 'SYSTEM',

                'created_at' => now(),
                'updated_at' => now(),
            ];

            // ======================
            // SIMPAN KE DATABASE
            // ======================
            $expedisi = Expedisi::create($expedisiData);

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data ekspedisi berhasil disimpan',
                'data' => [
                    'id' => $expedisi->id,
                    // 'NOMUAT' => $expedisi->NOMUAT,
                    'NOSJ' => $expedisi->NOSJ,
                    'JENISHRG' => $expedisi->JENISHRG,
                    'GRAND' => number_format($expedisi->GRAND, 0, ',', '.'),
                ],
                'nomuat' => $expedisi->NOMUAT // Kirim ke frontend untuk update field
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error simpan expedisi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data. Silahkan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getDataSurjal(Request $request){
        $expedisi = Expedisi::select([
            'id',
            'NOSJ',
            'tglsj',
            'NOMUAT',
            'CUSTOMER',
            'rute',
            'JUMLAH',
            'UNIT',
            'HARGA',
            'DISC',
            'DC',
            'GRAND',
            'KENDARAAN',
            'NAMA_KENDARAAN',
            'DRIVER',
            'NAMA_DRIVER',
            'STS',
            'JENISHRG',
            'created_at'
        ])->where('JENIS', 'EKS')
        // BELUM MUAT
        // ->where(function($query) {
        //     $query->where('NOMUAT', '')
        //         ->orWhereNull('NOMUAT');
        // })

        // BELUM INVOICE
        ->where(function($query) {
            $query->where('GB', '')
                ->orWhereNull('GB');
        })

        // BELUM INVOICE
        ->where(function($query) {
            $query->where('INVOICE', '')
                ->orWhereNull('INVOICE');
        })
        ->orderBy('created_at', 'desc');

        // 🔐 FILTER ROLE DRIVER
        if (auth()->user()->role_old === 'driver') {
            $expedisi->where('user_id', auth()->user()->user_id);
        }

        // Filter tanggal mulai
        if ($request->has('tgl_mulai') && !empty($request->tgl_mulai)) {
            $expedisi->whereDate('tglsj', '>=', $request->tgl_mulai);
        }

        // Filter tanggal akhir
        if ($request->has('tgl_akhir') && !empty($request->tgl_akhir)) {
            $expedisi->whereDate('tglsj', '<=', $request->tgl_akhir);
        }

        // Filter search (NO MUAT, CUSTOMER, RUTE)
        if ($request->has('search_muat') && !empty($request->search_muat)) {
            $search = $request->search_muat;
            $expedisi->where(function($query) use ($search) {
                $query->where('NOSJ', 'like', '%' . $search . '%')
                    ->orWhere('NOMUAT', 'like', '%' . $search . '%')
                    ->orWhere('CUSTOMER', 'like', '%' . $search . '%')
                    ->orWhere('rute', 'like', '%' . $search . '%');
            });
        }

        return DataTables::of($expedisi)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                $btn = '<div class="d-flex gap-2">'; // Gap lebih besar
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary px-3 py-1 pickSurjal"
                            data-id="'.$row->id.'" data-nosj="'.$row->NOSJ.'" title="Pilih">
                            <i class="bx bx-check" style="font-size: 14px;"></i>
                        </button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 deleteSurjal"
                            data-id="'.$row->id.'" data-nosj="'.$row->NOSJ.'" title="Hapus">
                            <i class="bx bx-trash" style="font-size: 14px;"></i>
                        </button>';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('total_formatted', function($row) {
                return 'Rp ' . number_format($row->GRAND, 0, ',', '.');
            })
            ->addColumn('harga_formatted', function($row) {
                return 'Rp ' . number_format($row->HARGA, 0, ',', '.');
            })
            ->addColumn('dc_formatted', function($row) {
                return 'Rp ' . number_format($row->DC, 0, ',', '.');
            })
            ->editColumn('TGLMUAT', function($row) {
                return $row->TGLMUAT ? date('d-m-Y', strtotime($row->TGLMUAT)) : '-';
            })
            ->editColumn('JUMLAH', function($row) {
                return number_format($row->JUMLAH, 0, ',', '.') . ' ' . $row->UNIT;
            })
            ->editColumn('DISC', function($row) {
                return $row->DISC ? $row->DISC . '%' : '-';
            })
            ->rawColumns(['action', 'total_formatted', 'harga_formatted', 'dc_formatted'])
            ->make(true);
    }

    public function showSurjal(Request $request){
        try {
            $id = $request->input('id');
            $nomuat = $request->input('nomuat');

            $query = Expedisi::query();

            if ($id) {
                $query->where('id', $id);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'ID atau NOSURJAL harus diisi'
                ], 400);
            }

            $expedisi = $query->first();

            if (!$expedisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Cek nilai customer untuk mengambil nama customer
            $customerName = DB::table('mcustomer')
                ->where('CUSTOMER', $expedisi->CUSTOMER_KODE)
                ->value('NAMACUST') ?? "";

            // Format data sesuai kebutuhan form
            $data = [
                // DATA DOKUMEN
                'tgl_muat' => $expedisi->TGLMUAT,
                'no_muat' => $expedisi->NOMUAT,
                'wilayah' => $expedisi->WILAYAH,
                'no_jalan' => $expedisi->NOJALAN,

                // Customer (asumsi ada relasi)
                'customer_id' => $expedisi->CUSTOMER_KODE,
                'customer_name' => $customerName,
                'customer' => $expedisi->CUSTOMER,
                'pesanan' => $expedisi->PESANAN,

                // KENDARAAN & DRIVER
                'kendaraan_id' => $expedisi->KENDARAAN,
                'kendaraan_nama' => $expedisi->NAMA_KENDARAAN,
                'tgl_sj' => $expedisi->tglsj,
                'no_sj' => $expedisi->NOSJ,

                // Driver
                'driver_1_id' => $expedisi->DRIVER,
                'driver_1_nama' => $expedisi->NAMA_DRIVER,
                'driver_2_id' => $expedisi->DRIVER2,
                'driver_2_nama' => $expedisi->NAMA_DRIVER2,

                // PENERIMA
                'penerima' => $expedisi->P_PENERIMA,
                'nama_penerima' => $expedisi->P_NAMA,
                'phone_penerima' => $expedisi->P_PHONE,
                'alamat_penerima' => $expedisi->P_ALAMAT,

                // BARANG
                'barang' => $expedisi->barang,
                'penyimpanan' => $expedisi->penyimpanan,
                'koli' => $expedisi->koli,
                'catatan' => $expedisi->catatan,

                // DETAIL & PERHITUNGAN
                'rute' => $expedisi->rute,
                'jumlah' => $expedisi->JUMLAH,
                'harga' => $expedisi->HARGA,
                'disc_percent' => $expedisi->DISC,
                'del_charge' => $expedisi->DC,
                'jenis_item' => $expedisi->JENISHRG,

                // Untuk perhitungan
                'sub_total' => $expedisi->TOTAL + $expedisi->NDISC,
                'dpp' => $expedisi->TOTAL,
                'ppn' => $expedisi->PPN,
                'grand_total' => $expedisi->GRAND
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Error get expedisi data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data'
            ], 500);
        }
    }

    // ExpedisiController.php
    public function updateSurjal(Request $request, $nosj){
        // ======================
        // VALIDASI (SAMA DENGAN STORE)
        // ======================
        $validator = Validator::make($request->all(), [
            'tglsj'  => 'required|date',
            'CUSTOMER' => 'required|string|max:30',
            'rute'     => 'required|string|max:30',
            'JUMLAH'   => 'required|numeric|min:0',
            'HARGA'    => 'required|numeric|min:0',
        ], [
            'tglsj.required'  => 'Tanggal muat harus diisi',
            'CUSTOMER.required' => 'Customer harus dipilih',
            'rute.required'     => 'Rute harus diisi',
            'JUMLAH.required'   => 'Jumlah harus diisi',
            'HARGA.required'    => 'Harga harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // ======================
            // AMBIL DATA EXISTING
            // ======================
            $expedisi = Expedisi::where('NOSJ', $nosj)->first();

            if (!$expedisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data expedisi tidak ditemukan'
                ], 404);
            }

            // ======================
            // VALIDASI SUDAH INVOICE / GB
            // ======================
            if (!empty($expedisi->INVOICE) || !empty($expedisi->GB)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah memiliki invoice / GB dan tidak dapat diedit.'
                ], 422);
            }

            // ======================
            // HITUNG ULANG (SAMA DENGAN STORE)
            // ======================
            $jumlah      = $request->JUMLAH ?? 0;
            $harga       = $request->HARGA ?? 0;
            $discPercent = $request->DISC ?? 0;
            $delCharge   = $request->DC ?? 0;
            $ppnPercent  = floatval($request->PPN ?? 11);
            $subTotal = round($jumlah * $harga);
            $discAmount = round($subTotal * ($discPercent / 100));
            $totalAfterDisc = round($subTotal - $discAmount);
            $ppn = round($totalAfterDisc * ($ppnPercent / 100));
            $grandTotal = round($totalAfterDisc + $ppn + $delCharge);
            // ======================
            // PREPARE DATA UPDATE (MIRROR STORE)
            // ======================
            $expedisiData = [
                // IDENTITAS
                // 'TGLMUAT'        => $request->TGLMUAT,
                // 'NOJALAN'        => $request->NOJALAN,
                'WILAYAH'        => $request->WILAYAH ?? $expedisi->WILAYAH,
                'CUSTOMER_KODE' => $request->customer_expedisi_id,
                'CUSTOMER'      => $request->CUSTOMER,
                'PESANAN'       => $request->item_expedisi,

                // KENDARAAN
                'KENDARAAN'      => $request->kendaraan_expedisi_id,
                'NAMA_KENDARAAN' => $request->NAMA_KENDARAAN,
                'tglsj'          => $request->tglsj,
                // 'NOSJ'           => $request->NOSJ,
                'DRIVER'         => $request->driver_1_expedisi_id,
                'NAMA_DRIVER'    => $request->NAMA_DRIVER,
                'DRIVER2'        => $request->driver_2_expedisi_id,
                'NAMA_DRIVER2'   => $request->NAMA_DRIVER2,

                // PENERIMA
                'P_PENERIMA' => $request->P_PENERIMA,
                'P_NAMA'     => $request->P_NAMA,
                'P_PHONE'    => $request->P_PHONE,
                'P_ALAMAT'   => $request->P_ALAMAT,

                // BARANG
                'barang' => $request->barang,
                'penyimpanan' => $request->penyimpanan,
                'koli' => $request->koli,
                'catatan' => $request->catatan,

                // DETAIL & HITUNGAN
                'rute'     => $request->rute,
                'JUMLAH'   => $jumlah,
                'UNIT'     => $request->UNIT ?? 'KG',
                'HARGA'    => $harga,
                'hargaaw'  => $request->hargaaw ?? $harga,
                'DISC'     => $discPercent,
                'DC'       => $delCharge,
                'DCAW'     => $request->DCAW ?? $delCharge,
                'NDISC'    => $discAmount,
                'NDISCAW'  => $discAmount,
                'TOTAL'    => $totalAfterDisc,
                'PPN'      => $ppnPercent,
                'GRAND'    => $grandTotal,

                // STATUS
                'JENISHRG'  => $request->JENISHRG ?? $expedisi->JENISHRG,
                'JENIS'     => $request->JENIS ?? $expedisi->JENIS,
                // 'STS'       => $request->STS ?? $expedisi->STS,
                'SIMPAN'    => $request->SIMPAN ?? $expedisi->SIMPAN,
                'READY'     => $request->READY ?? $expedisi->READY,
                'CLOSSING'  => $request->CLOSSING ?? $expedisi->CLOSSING,
                'KETERANGAN'=> $request->KETERANGAN ?? $expedisi->KETERANGAN,

                // USER
                'USEREDIT'  => auth()->user()->name ?? 'SYSTEM',
                'updated_at'=> now(),
            ];

            // ======================
            // UPDATE
            // ======================
            $expedisi->update($expedisiData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data ekspedisi berhasil diupdate',
                'data' => [
                    'id' => $expedisi->id,
                    'NOMUAT' => $expedisi->NOMUAT,
                    'NOSJ'   => $expedisi->NOSJ,
                    'GRAND'  => number_format($expedisi->GRAND, 0, ',', '.'),
                    'updated_at' => $expedisi->updated_at->format('d-m-Y H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error update expedisi', [
                'NOMUAT'  => $nomuat,
                'error'   => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data. Silakan coba lagi.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroySurjal($id){
        $data = Expedisi::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function showMuat(Request $request){
        try {
            $nomuat = $request->input('nomuat');

            if (!$nomuat) {
                return response()->json([
                    'success' => false,
                    'message' => 'NOMUAT wajib diisi'
                ], 400);
            }

            // 🔹 Ambil SEMUA baris dengan NOMUAT yang sama
            $expedisi = Expedisi::where('NOMUAT', $nomuat)
                ->where('JENIS', 'EKS')
                ->where(function($q) {
                    $q->where('INVOICE', '')
                            ->orWhereNull('INVOICE');
                })
                ->orderBy('id', 'desc')
                ->get();

            if ($expedisi->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // 🔹 Mapping data ke format frontend
            $rows = $expedisi->map(function ($row) {
            return [
                'id'                    => $row->id,
                'nomuat'                => $row->NOMUAT,
                'tglmuat'               => $row->TGLMUAT,
                'rute'                  => $row->rute,
                'plat_nomor'            => $row->PLAT_NOMOR,
                'driver1_kode'          => $row->DRIVER,
                'driver1'               => $row->NAMA_DRIVER,
                'driver2_kode'          => $row->DRIVER2,
                'driver2'               => $row->NAMA_DRIVER2,
                'kendaraan_kode'        => $row->KENDARAAN,
                'kendaraan'             => $row->NAMA_KENDARAAN,
                'km_awal'               => $row->KM_AWAL,
                'km_akhir'              => $row->KM_AKHIR,
                'uang_jalan'            => $row->UANG_JALAN,
                'uang_driver_makan'     => $row->UANG_DRIVER_MAKAN,
                'uang_lain_lain'        => $row->UANG_LAIN_LAIN,
                'pengirim'              => $row->PENGIRIM,

                'ac_kendaraan'          => $row->AC_KENDARAAN,

                'nosj'                  => $row->NOSJ,
                'tgl_sj'                => $row->tglsj,
                'dc'                    => $row->DC,
                'jumlah'                => $row->JUMLAH,
                'unit'                  => $row->UNIT,
                'jenis'                 => $row->JENIS,
                'jenishrg'              => $row->JENISHRG,
                'harga'                 => $row->HARGA,
                'disc'                  => $row->DISC,
                'ppn'                   => $row->PPN,
                'total'                 => $row->GRAND,
            ];
        });

            return response()->json([
                'success' => true,
                'nomuat'  => $nomuat,
                'data'    => $rows
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    public function storeMuat(Request $request)
    {
        $request->validate([
            'nosj' => 'required|array|min:1',
        ]);
        $nomuat = null;
        DB::transaction(function () use ($request, &$nomuat) {
            $nomuat = $this->generateNomuatWithLock();
            $nojalan = $this->generateNoJalan();
            $tglMuat = $request->tglmuat ?? now();

            DB::table('expedisi')
                ->whereIn('NOSJ', $request->nosj)
                ->update([
                    'NOMUAT'               => $nomuat,
                    'TGLMUAT'              => $tglMuat,
                    'NOJALAN'              => $nojalan,
                    'rute'                 => $request->rute,
                    'NAMA_DRIVER'          => $request->driver1,
                    'NAMA_DRIVER2'         => $request->driver2,
                    'NAMA_KENDARAAN'       => $request->kendaraan,
                    'PLAT_NOMOR'           => $request->plat_nomor,
                    'DRIVER'               => $request->driver1_kode,
                    'DRIVER2'              => $request->driver2_kode,
                    'KENDARAAN'            => $request->kendaraan_kode,
                    'KM_AWAL'              => $request->km_awal,
                    'KM_AKHIR'             => $request->km_akhir,
                    'UANG_JALAN'           => $request->uang_jalan,
                    'UANG_DRIVER_MAKAN'    => $request->uang_driver_makan,
                    'UANG_LAIN_LAIN'       => $request->uang_lain_lain,
                    'updated_at'           => now()
                ]);

            // ==========================
            // UPDATE PENGIRIM PER ROW
            // ==========================

            $pengirimData = $request->pengirim ?? [];
            if (!empty($pengirimData)) {
                foreach ($pengirimData as $row) {
                    if (!empty($row['nosj'])) {
                        DB::table('expedisi')
                            ->where('NOSJ', $row['nosj'])
                            ->update([
                                'PENGIRIM' => $row['pengirim'] ?? ''
                            ]);
                    }
                }
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Data expedisi berhasil disimpan',
            'nomuat' => $nomuat
        ]);
    }

    public function updateMuat(Request $request, $nomuat)
    {
        $request->validate([
            'nosj' => 'required|array|min:1',
        ]);
        DB::transaction(function () use ($request, $nomuat) {
            $nosjBaru = $request->nosj;
            // =========================
            // NOSJ LAMA
            // =========================
            $nosjLama = DB::table('expedisi')
                ->where('NOMUAT', $nomuat)
                ->pluck('NOSJ')
                ->toArray();
            // =========================
            // SELISIH DATA
            // =========================
            $nosjTambah = array_diff($nosjBaru, $nosjLama);
            $nosjHapus  = array_diff($nosjLama, $nosjBaru);
            // =========================
            // UPDATE HEADER SEMUA ROW
            // =========================
            DB::table('expedisi')
                ->where('NOMUAT', $nomuat)
                ->update([
                    'TGLMUAT'              => $request->tglmuat,
                    'rute'                 => $request->rute,
                    'PLAT_NOMOR'           => $request->plat_nomor,
                    'DRIVER'               => $request->driver1_kode,
                    'NAMA_DRIVER'          => $request->driver1,
                    'DRIVER2'              => $request->driver2_kode,
                    'NAMA_DRIVER2'         => $request->driver2,
                    'KENDARAAN'            => $request->kendaraan_kode,
                    'NAMA_KENDARAAN'       => $request->kendaraan,
                    'KM_AWAL'              => $request->km_awal,
                    'KM_AKHIR'             => $request->km_akhir,
                    'UANG_JALAN'           => $request->uang_jalan,
                    'UANG_DRIVER_MAKAN'    => $request->uang_driver_makan,
                    'UANG_LAIN_LAIN'       => $request->uang_lain_lain,
                    'updated_at'           => now()
                ]);

            // =========================
            // TAMBAH SJ BARU
            // =========================
            if (!empty($nosjTambah)) {

                DB::table('expedisi')
                    ->whereIn('NOSJ', $nosjTambah)
                    ->update([
                        'NOMUAT'               => $nomuat,
                        'TGLMUAT'              => $request->tglmuat,
                        'rute'                 => $request->rute,
                        'PLAT_NOMOR'           => $request->plat_nomor,
                        'DRIVER'               => $request->driver1_kode,
                        'NAMA_DRIVER'          => $request->driver1,
                        'DRIVER2'              => $request->driver2_kode,
                        'NAMA_DRIVER2'         => $request->driver2,
                        'KENDARAAN'            => $request->kendaraan_kode,
                        'NAMA_KENDARAAN'       => $request->kendaraan,
                        'KM_AWAL'              => $request->km_awal,
                        'KM_AKHIR'             => $request->km_akhir,
                        'UANG_JALAN'           => $request->uang_jalan,
                        'UANG_DRIVER_MAKAN'    => $request->uang_driver_makan,
                        'UANG_LAIN_LAIN'       => $request->uang_lain_lain,
                        'updated_at'           => now()
                    ]);
            }

            // =========================
            // HAPUS DARI MUATAN
            // =========================
            if (!empty($nosjHapus)) {
                DB::table('expedisi')
                    ->whereIn('NOSJ', $nosjHapus)
                    ->update([
                        'NOMUAT'               => null,
                        'TGLMUAT'              => null,
                        'rute'                 => null,
                        'PLAT_NOMOR'           => null,
                        'DRIVER'               => null,
                        'NAMA_DRIVER'          => null,
                        'DRIVER2'              => null,
                        'NAMA_DRIVER2'         => null,
                        'KENDARAAN'            => null,
                        'NAMA_KENDARAAN'       => null,
                        'KM_AWAL'              => null,
                        'KM_AKHIR'             => null,
                        'UANG_JALAN'           => null,
                        'UANG_DRIVER_MAKAN'    => null,
                        'UANG_LAIN_LAIN'       => null,
                        'updated_at'           => now()
                    ]);
            }
            // =========================
            // UPDATE PENGIRIM PER ROW
            // =========================
            $pengirimData = $request->pengirim ?? [];
            if (!empty($pengirimData)) {
                foreach ($pengirimData as $row) {
                    if (!empty($row['nosj'])) {
                        DB::table('expedisi')
                            ->where('NOSJ', $row['nosj'])
                            ->update([
                                'PENGIRIM' => $row['pengirim'] ?? ''
                            ]);
                    }
                }
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Data muatan berhasil diperbarui'
        ]);
    }

    public function getDataMuat(Request $request){
        // 🔹 Subquery: ambil BARIS PERTAMA per NOMUAT
        $subQuery = DB::table('expedisi')
            ->select(DB::raw('MIN(id) as id'))
            ->where('JENIS', 'EKS')
            ->whereNotNull('NOMUAT')
            // Tambahan Biar Yang SUdah Ivoice Tidak Tampil
            ->where(function ($query) {
                $query->whereNull('INVOICE')
                    ->orWhere('INVOICE', '=', '');
            })
            ->groupBy('NOMUAT');

        $expedisi = Expedisi::select([
            'id',
            'NOMUAT',
            'TGLMUAT',
            'CUSTOMER',
            'rute',
            'JUMLAH',
            'UNIT',
            'HARGA',
            'DISC',
            'DC',
            'GRAND',
            'NOSJ',
            'KENDARAAN',
            'NAMA_KENDARAAN',
            'DRIVER',
            'NAMA_DRIVER',
            'STS',
            'created_at'
        ])->whereNotNull('NOMUAT')
        ->whereIn('id', $subQuery)
        // ->orderBy('id', 'desc');
        ->orderBy('TGLMUAT', 'desc');

        // 🔐 FILTER ROLE DRIVER
        if (auth()->user()->role_old === 'driver') {
            $expedisi->where('user_id', auth()->user()->user_id);
        }

        // 📅 FILTER TANGGAL MULAI
        if ($request->filled('tgl_mulai')) {
            $expedisi->whereDate('TGLMUAT', '>=', $request->tgl_mulai);
        }

        // 📅 FILTER TANGGAL AKHIR
        if ($request->filled('tgl_akhir')) {
            $expedisi->whereDate('TGLMUAT', '<=', $request->tgl_akhir);
        }

        // 🔍 SEARCH
        if ($request->filled('search_muat')) {
            $search = $request->search_muat;
            $expedisi->where(function ($q) use ($search) {
                $q->where('NOMUAT', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('rute', 'like', "%{$search}%")
                ->orWhere('NOSJ', 'like', "%{$search}%");
            });
        }

        return DataTables::of($expedisi)
            ->addIndexColumn()

            // 🔘 ACTION BUTTON
            ->addColumn('action', function ($row) {
                $btn = '<div class="d-flex gap-2">'; // Gap lebih besar
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary px-3 py-1 pickMuat"
                            data-id="'.$row->id.'"
                            data-nomuat="'.$row->NOMUAT.'"
                            title="Pilih">
                            <i class="bx bx-check" style="font-size: 14px;"></i>
                        </button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 deleteMuat"
                            data-id="'.$row->id.'"
                            data-nomuat="'.$row->NOMUAT.'"
                            title="Hapus">
                            <i class="bx bx-trash" style="font-size: 14px;"></i>
                        </button>';
                $btn .= '</div>';
                return $btn;
            })

            // 💰 FORMAT HARGA
            ->addColumn('harga_formatted', function ($row) {
                return 'Rp ' . number_format($row->HARGA ?? 0, 0, ',', '.');
            })

            ->addColumn('dc_formatted', function ($row) {
                return 'Rp ' . number_format($row->DC ?? 0, 0, ',', '.');
            })

            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->GRAND ?? 0, 0, ',', '.');
            })

            // ✏️ EDIT KOLOM
            ->editColumn('TGLMUAT', function ($row) {
                return $row->TGLMUAT
                    ? date('d-m-Y', strtotime($row->TGLMUAT))
                    : '-';
            })

            ->editColumn('JUMLAH', function ($row) {
                return number_format($row->JUMLAH ?? 0, 0, ',', '.') . ' ' . ($row->UNIT ?? '');
            })

            ->editColumn('DISC', function ($row) {
                return $row->DISC ? $row->DISC . '%' : '-';
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    public function destroyMuat($nomuat)
    {
        // =============================
        // GUARD CEK GB / INVOICE
        // =============================
        $locked = Expedisi::where('NOMUAT', $nomuat)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('GB')
                    ->where('GB', '!=', '');
                })
                ->orWhere(function ($q2) {
                    $q2->whereNotNull('INVOICE')
                    ->where('INVOICE', '!=', '');
                });
            })
            ->exists();
        if ($locked) {
            return response()->json([
                'success' => false,
                'message' => 'Muatan tidak bisa dibatalkan karena sudah tergabung / sudah invoice'
            ], 400);
        }
        // =============================
        // RESET MUATAN
        // =============================
        $updated = Expedisi::where('NOMUAT', $nomuat)
            ->update([
                'NOMUAT'               => null,
                'TGLMUAT'              => null,
                'NOJALAN'              => null,
                'rute'                 => null,
                'PLAT_NOMOR'           => null,
                'DRIVER'               => null,
                'NAMA_DRIVER'          => null,
                'DRIVER2'              => null,
                'NAMA_DRIVER2'         => null,
                'KENDARAAN'            => null,
                'NAMA_KENDARAAN'       => null,
                'KM_AWAL'              => null,
                'KM_AKHIR'             => null,
                'UANG_JALAN'           => null,
                'UANG_DRIVER_MAKAN'    => null,
                'UANG_LAIN_LAIN'       => null,
                // pengirim ikut reset kalau mau bersih total
                // kalau mau tetap simpan history pengirim, hapus bagian ini
                'PENGIRIM'             => null,
                'updated_at'           => now()
            ]);
        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Muatan berhasil dibatalkan'
        ]);
    }

    public function getRuteMuat(){
        $data = Rute::select('id', 'RUTE', 'created_at');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '
                     <div class="d-flex gap-1">
                        <button onclick="pickDataRuteMuat('.$row->id.', \''.$row->RUTE.'\')" class="btn btn-primary btn-sm me-1">Pilih</button>
                    </div>
                ';
                return $btn;
            })
            ->editColumn('created_at', function($row){
                return $row->created_at->format('d/m/Y H:i');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getKmTerakhir(Request $request)
    {
        $data = Expedisi::where('KENDARAAN', $request->kendaraan)
            ->latest('id')
            ->first();

        return response()->json([
            'km_akhir' => $data?->KM_AKHIR ?? 0
        ]);
    }

    public function pdfMuat($nomuat)
    {
        $data = Expedisi::where('NOMUAT', $nomuat)
            ->orderBy('id')
            ->get();

        if ($data->isEmpty()) {
            abort(404);
        }

        $header = $data->first();

        $tempPath = storage_path('app/mpdf-temp');

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'format'  => 'A4',
            'tempDir' => $tempPath,
        ]);

        $html = view('expedisi.expedisi-muat-pdf', compact(
            'data',
            'header'
        ))->render();

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output("Laporan-Muat-$nomuat.pdf", 'I')
        )->header('Content-Type', 'application/pdf');
    }

    private function generateNomuatWithLock(){
        return DB::transaction(function () {
            $bulan = now()->format('m');
            $tahun = now()->format('y');
            $prefix = 'MU' . $bulan . $tahun;

            $last = Expedisi::where('NOMUAT', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('NOMUAT', 'desc')
                ->first();

            $next = $last
                ? ((int) substr($last->NOMUAT, -5) + 1)
                : 1;

            return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
        });
    }

    private function generateNoSuratJalan(){
        return DB::transaction(function () {

            $bulan = now()->format('m');
            $tahun = now()->format('y');

            $prefix = 'SJ' . $bulan . $tahun;

            $last = Expedisi::where('NOSJ', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('NOSJ', 'desc')
                ->first();

            if ($last) {
                $lastNumber = (int) substr($last->NOSJ, -5);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $number = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            return $prefix . $number;
        });
    }

    private function generateNoJalan(){
        return DB::transaction(function () {

            $bulan  = now()->format('m');
            $tahun  = now()->format('y');
            $prefix = $bulan . $tahun; // contoh: 0126

            $last = Expedisi::where('NOJALAN', 'like', 'PJ' . $prefix . '%')
                ->lockForUpdate()
                ->orderBy('NOJALAN', 'desc')
                ->first();

            $nextNumber = $last
                ? ((int) substr($last->NOJALAN, -5) + 1)
                : 1;

            $number = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            return 'PJ' . $prefix . $number;
        });
    }

    // Print PDF
    public function printSuratJalan($id){
        $expedisi = Expedisi::findOrFail($id);

        $expedisi->PLAT = DB::table('kendaraan')
            ->where('KODE', $expedisi->KENDARAAN)
            ->value('PLAT');

        $user = auth()->user();

        // 🔐 CEK AKSES
        if ($user->role_old !== 'admin') {
            if ($expedisi->user_id !== $user->user_id) {
                abort(403, 'Anda tidak berhak mencetak surat jalan ini');
            }
        }

        $tempPath = storage_path('app/mpdf-temp');

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'tempDir' => $tempPath,
        ]);

        $html = view('expedisi.expedisi-surjal-pdf', compact('expedisi'))->render();

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output(
                'Surat-Jalan-' . $expedisi->NOSJ . '.pdf',
                'I'
            )
        )->header('Content-Type', 'application/pdf');
    }
}
