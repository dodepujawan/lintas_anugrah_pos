<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Expedisi;
use App\Models\Mcustomer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class RentPendinginController extends Controller
{
    public function index()
    {
        return view('rentPendingin.rentPendingin');
    }

    public function getDataMuat(Request $request){
        // 🔹 Subquery: ambil BARIS PERTAMA per NOMUAT
        $subQuery = DB::table('expedisi')
            ->select(DB::raw('MIN(id) as id'))
            ->where('JENIS', 'REN')
            ->whereNotNull('NOMUAT')
            ->whereNull('kwt')
            ->whereNull('INVOICE')
            ->groupBy('NOMUAT');

        $expedisi = Expedisi::select([
            'id',
            'NOMUAT',
            'TGLMUAT',
            'CUSTOMER',
            'PESANAN',
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
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary px-3 py-1 pickRentDgn"
                            data-id="'.$row->id.'"
                            data-nomuat="'.$row->NOMUAT.'"
                            title="Pilih">
                            <i class="bx bx-check" style="font-size: 14px;"></i>
                        </button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 deleteRentDgn"
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

    public function destroy($id){
        $data = Expedisi::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Reset ke state surjal: kosongkan NOMUAT & TGLMUAT
        $data->update([
            'NOMUAT' => null,
            'TGLMUAT' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dikembalikan ke surat jalan'
        ]);
    }

    public function getDataSurjal(Request $request){
        $expedisi = Expedisi::select([
            'id',
            'NOSJ',
            'tglsj',
            'CUSTOMER',
            'rute',
            'PESANAN',
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
        ])->where('JENIS', 'REN')
        ->where(function($query) {
            $query->where('NOMUAT', '')
                    ->orWhereNull('NOMUAT');
        })
        ->orderBy('created_at', 'desc'); ;

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
                    ->orWhere('CUSTOMER', 'like', '%' . $search . '%')
                    ->orWhere('rute', 'like', '%' . $search . '%');
            });
        }

        return DataTables::of($expedisi)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                $btn = '<div class="d-flex gap-2">'; // Gap lebih besar
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary px-3 py-1 pickSurjalRentDgn"
                            data-id="'.$row->id.'" data-nosj="'.$row->NOSJ.'" title="Pilih">
                            <i class="bx bx-check" style="font-size: 14px;"></i>
                        </button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 deleteSurjalRentDgn"
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

    public function getDataCustomer(){
        $customers = Mcustomer::select(['id', 'kode_cus','CUSTOMER', 'NAMACUST', 'TYPECUST', 'TELEPON', 'EMAIL', 'ALAMAT1', 'TELEPON', 'created_at']);

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function($customer) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-btn-customer-rent-dingin" data-id="'.$customer->CUSTOMER.'" data-name="'.$customer->NAMACUST.'" data-customer="'.$customer->CUSTOMER.'" data-alamat="'.$customer->ALAMAT1.'" data-telepon="'.$customer->TELEPON.'" data-bs-toggle="tooltip" title="View">
                            <i class="bx bx-check"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeRentPendinginSurjal(Request $request){
        $validator = Validator::make($request->all(), [
            'tanggal_surjal_rent_dingin' => 'required|date',
            'customer_rent_dingin' => 'required|string',
            'jml_hari_rent_dingin' => 'required|numeric|min:1',
            'harga_rent_dingin' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // ======================
            // GENERATE NOSJ
            // ======================
            $nosj = $this->generateNoSuratJalan();

            // ======================
            // HITUNG ULANG (SERVER SIDE)
            // ======================
            $jumlah = (float) ($request->jml_hari_rent_dingin ?? 0);
            $harga = (float) ($request->harga_rent_dingin ?? 0);
            $discPercent = (float) ($request->discount_rent_dingin ?? 0);
            $ppnPercent = (float) ($request->pajak_rent_dingin ?? 0);

            $subTotal = round($jumlah * $harga);
            $discAmount = round($subTotal * ($discPercent / 100));
            $discAmount = min($discAmount, $subTotal);
            $dpp = round($subTotal - $discAmount);
            $ppn = round($dpp * ($ppnPercent / 100));
            $grandTotal = round($dpp + $ppn);

            // ======================
            // SIMPAN DATA
            // ======================
            $expedisi = Expedisi::create([
                // DOKUMEN
                'tglsj' => $request->tanggal_surjal_rent_dingin,
                'NOSJ' => $nosj,
                'WILAYAH' => $request->wilayah_nosj_rent_dingin,

                // CUSTOMER
                'CUSTOMER_KODE' => $request->customer_rent_dingin_id,
                'CUSTOMER' => $request->customer_rent_dingin,
                'P_PENERIMA' => $request->nama_penerima_rent_dingin,
                'P_ALAMAT' => $request->alamat_rent_dingin,
                'P_NAMA' => $request->nama_penerima_rent_dingin,
                'P_PHONE' => $request->telpon_rent_dingin,


                // ITEM RENT
                'PESANAN' => $request->item_rent_dingin,

                // DRIVER & KENDARAAN
                'DRIVER' => $request->driver_rent_dingin_id,
                'NAMA_DRIVER' => $request->driver_rent_dingin,
                'KENDARAAN' => $request->kendaraan_rent_dingin_id,
                'NAMA_KENDARAAN' => $request->kendaraan_rent_dingin,

                // PERHITUNGAN
                'JUMLAH' => $jumlah,
                'UNIT' => 'HARI',
                'HARGA' => $harga,
                'DISC' => $discPercent,
                'NDISC' => $discAmount,
                'TOTAL' => $dpp,
                'PPN' => $ppnPercent,
                'GRAND' => $grandTotal,

                // STATUS
                'JENIS' => 'REN',
                // 'STS' => 'INVOICE',
                'READY' => 'Y',
                'CLOSSING' => 'N',
                'KETERANGAN' => $request->KETERANGAN,

                // KETERANGAN
                'catatan' => $request->keterangan_rent_dingin ?? 'RENT PENDINGIN',

                // USER
                'user_id' => auth()->user()->user_id,
                'user' => auth()->user()->name,
                // AREA
                'area_id' => auth()->user()->area_id,
                'area_name' => auth()->user()->area_name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rent pendingin berhasil disimpan',
                'data' => [
                    'NOSJ' => $expedisi->NOSJ,
                    'GRAND' => number_format($expedisi->GRAND, 0, ',', '.'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan rent pendingin',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateRentPendinginSurjal(Request $request, $nosj){
        $validator = Validator::make($request->all(), [
            'tanggal_surjal_rent_dingin' => 'required|date',
            'customer_rent_dingin' => 'required|string',
            'jml_hari_rent_dingin' => 'required|numeric|min:1',
            'harga_rent_dingin' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $expedisi = Expedisi::where('NOSJ', $nosj)->lockForUpdate()->firstOrFail();
            // ======================
            // VALIDASI SUDAH INVOICE / GB
            // ======================
            if (!empty($expedisi->INVOICE) || !empty($expedisi->GB)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah memiliki invoice / GB dan tidak dapat diedit.'
                ], 422);
            }

            // ======================
            // HITUNG ULANG
            // ======================
            $jumlah = (float) ($request->jml_hari_rent_dingin ?? 0);
            $harga  = (float) ($request->harga_rent_dingin ?? 0);
            $discPercent = (float) ($request->discount_rent_dingin ?? 0);
            $ppnPercent  = (float) ($request->pajak_rent_dingin ?? 0);
            $subTotal = round($jumlah * $harga);
            $discAmount = round($subTotal * ($discPercent / 100));
            $discAmount = min($discAmount, $subTotal);
            $dpp = round($subTotal - $discAmount);
            $ppn = round($dpp * ($ppnPercent / 100));
            $grandTotal = round($dpp + $ppn);
            // ======================
            // UPDATE DATA
            // ======================
            $expedisi->update([
                'tglsj' => $request->tanggal_surjal_rent_dingin,
                'WILAYAH' => $request->wilayah_nosj_rent_dingin,

                'CUSTOMER_KODE' => $request->customer_rent_dingin_id,
                'CUSTOMER' => $request->customer_rent_dingin,
                'P_PENERIMA' => $request->nama_penerima_rent_dingin,
                'P_ALAMAT' => $request->alamat_rent_dingin,
                'P_NAMA' => $request->nama_penerima_rent_dingin,
                'P_PHONE' => $request->telpon_rent_dingin,

                'PESANAN' => $request->item_rent_dingin,

                'DRIVER' => $request->driver_rent_dingin_id,
                'NAMA_DRIVER' => $request->driver_rent_dingin,
                'KENDARAAN' => $request->kendaraan_rent_dingin_id,
                'NAMA_KENDARAAN' => $request->kendaraan_rent_dingin,

                'JUMLAH' => $jumlah,
                'HARGA' => $harga,
                'DISC' => $discPercent,
                'NDISC' => $discAmount,
                'TOTAL' => $dpp,
                'PPN' => $ppnPercent,
                'GRAND' => $grandTotal,

                'KETERANGAN' => $request->KETERANGAN,
                'catatan' => $request->keterangan_rent_dingin ?? 'RENT PENDINGIN',

                'user_id' => auth()->user()->user_id,
                'user' => auth()->user()->name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data rent pendingin berhasil diperbarui',
                'data' => [
                    'NOSJ' => $expedisi->NOSJ,
                    'GRAND' => number_format($expedisi->GRAND, 0, ',', '.'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function showSurjal($nosj){
        $data = Expedisi::where('NOSJ', $nosj)
            ->where('JENIS', 'REN')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
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

    public function updateRentPendinginMuat(Request $request, $nosj){
        $validator = Validator::make($request->all(), [
            'tanggal_surjal_rent_dingin' => 'required|date',
            'customer_rent_dingin' => 'required|string',
            'jml_hari_rent_dingin' => 'required|numeric|min:1',
            'harga_rent_dingin' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $expedisi = Expedisi::where('NOSJ', $nosj)->lockForUpdate()->firstOrFail();

            // ======================
            // HITUNG ULANG
            // ======================
            $jumlah = (int) $request->jml_hari_rent_dingin;
            $harga  = (int) $request->harga_rent_dingin;

            $discPercent = (float) ($request->discount_rent_dingin ?? 0);
            $ppnPercent  = (float) ($request->pajak_rent_dingin ?? 0);

            $subTotal = $jumlah * $harga;
            $discAmount = round($subTotal * ($discPercent / 100));
            $discAmount = min($discAmount, $subTotal);
            $dpp = $subTotal - $discAmount;
            $ppn = round($dpp * ($ppnPercent / 100));
            $grandTotal = $dpp + $ppn;

            // ======================
            // TENTUKAN NOMUAT
            // ======================
            $nomuat = $expedisi->NOMUAT;

            if (empty($nomuat)) {
                $nomuat = $this->generateNomuatWithLock();
            }

            // ======================
            // UPDATE DATA
            // ======================
            $expedisi->update([
                'NOMUAT' => $nomuat,
                'TGLMUAT' => $request->tanggal_rent_dingin,

                'tglsj' => $request->tanggal_surjal_rent_dingin,
                'WILAYAH' => $request->wilayah_nosj_rent_dingin,

                'CUSTOMER_KODE' => $request->customer_rent_dingin_id,
                'CUSTOMER' => $request->customer_rent_dingin,
                'P_PENERIMA' => $request->nama_penerima_rent_dingin,
                'P_ALAMAT' => $request->alamat_rent_dingin,
                'P_NAMA' => $request->nama_penerima_rent_dingin,
                'P_PHONE' => $request->telpon_rent_dingin,

                'PESANAN' => $request->item_rent_dingin,

                'DRIVER' => $request->driver_rent_dingin_id,
                'NAMA_DRIVER' => $request->driver_rent_dingin,
                'KENDARAAN' => $request->kendaraan_rent_dingin_id,
                'NAMA_KENDARAAN' => $request->kendaraan_rent_dingin,

                'JUMLAH' => $jumlah,
                'HARGA' => $harga,
                'DISC' => $discPercent,
                'NDISC' => $discAmount,
                'TOTAL' => $dpp,
                'PPN' => $ppnPercent,
                'GRAND' => $grandTotal,

                'KETERANGAN' => $request->KETERANGAN,
                'catatan' => $request->keterangan_rent_dingin ?? 'RENT PENDINGIN',

                'user_id' => auth()->user()->user_id,
                'user' => auth()->user()->name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data rent pendingin berhasil diperbarui',
                'data' => [
                    'NOSJ' => $expedisi->NOSJ,
                    'GRAND' => number_format($expedisi->GRAND, 0, ',', '.'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function showMuat($nomuat){
        $data = Expedisi::where('NOMUAT', $nomuat)
            ->where('JENIS', 'REN')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function destroyMuat($id){
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

    public function printSurjalRent($nosj){
        try {
            // Ambil data expedisi berdasarkan NOSJ (nomor surat jalan)
            $expedisi = Expedisi::where('NOSJ', $nosj)->firstOrFail();

            $expedisi->PLAT = DB::table('kendaraan')
            ->where('KODE', $expedisi->KENDARAAN)
            ->value('PLAT');

            // Pastikan ini adalah data RENT
            if ($expedisi->JENIS !== 'REN') {
                abort(404, 'Data bukan tipe RENT');
            }

            $tempPath = storage_path('app/mpdf-temp');

            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0775, true);
            }

            $mpdfConfig = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font_size' => 10,
                'default_font' => 'dejavusans',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_header' => 5,
                'margin_footer' => 5,
                'orientation' => 'P',
                'tempDir' => $tempPath,   // <-- tambahkan ini
            ];
            // Inisialisasi mPDF
            $mpdf = new Mpdf($mpdfConfig);

            // Set metadata
            $mpdf->SetTitle("Surat Jalan Sewa Unit - {$expedisi->NOSJ}");
            $mpdf->SetAuthor("PT. Lintas Mitra Anugerah Sejati");

            // Render view Blade ke HTML
            $html = view('rentPendingin.rentPendingin-surjal-pdf', compact('expedisi'))->render();

            // Write HTML content
            $mpdf->WriteHTML($html);

            // Output PDF
            $filename = "Surjal_Rent_{$expedisi->NOSJ}.pdf";

            // Untuk preview di browser
            return $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Surat Jalan tidak ditemukan');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate PDF',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}

// public function getDataMuat1(Request $request){
    //     $expedisi = Expedisi::select([
    //         'id',
    //         'NOMUAT',
    //         'TGLMUAT',
    //         'CUSTOMER',
    //         'rute',
    //         'JUMLAH',
    //         'UNIT',
    //         'HARGA',
    //         'DISC',
    //         'DC',
    //         'GRAND',
    //         'NOSJ',
    //         'KENDARAAN',
    //         'NAMA_KENDARAAN',
    //         'DRIVER',
    //         'NAMA_DRIVER',
    //         'STS',
    //         'created_at'
    //     ])->where('JENIS', 'REN');

    //     // 🔐 FILTER ROLE DRIVER
    //     if (auth()->user()->roles === 'driver') {
    //         $expedisi->where('user_id', auth()->user()->user_id);
    //     }

    //     // Filter tanggal mulai
    //     if ($request->has('tgl_mulai') && !empty($request->tgl_mulai)) {
    //         $expedisi->whereDate('TGLMUAT', '>=', $request->tgl_mulai);
    //     }

    //     // Filter tanggal akhir
    //     if ($request->has('tgl_akhir') && !empty($request->tgl_akhir)) {
    //         $expedisi->whereDate('TGLMUAT', '<=', $request->tgl_akhir);
    //     }

    //     // Filter search (NO MUAT, CUSTOMER, RUTE)
    //     if ($request->has('search_muat') && !empty($request->search_muat)) {
    //         $search = $request->search_muat;
    //         $expedisi->where(function($query) use ($search) {
    //             $query->where('NOMUAT', 'like', '%' . $search . '%')
    //                 ->orWhere('CUSTOMER', 'like', '%' . $search . '%')
    //                 ->orWhere('rute', 'like', '%' . $search . '%')
    //                 ->orWhere('NOSJ', 'like', '%' . $search . '%');
    //         });
    //     }

    //     return DataTables::of($expedisi)
    //         ->addIndexColumn()
    //         ->addColumn('action', function($row) {
    //             $btn = '<div class="d-flex gap-2">'; // Gap lebih besar
    //             $btn .= '<button type="button" class="btn btn-sm btn-outline-primary px-3 py-1 pickRentDgn"
    //                         data-id="'.$row->id.'" data-nomuat="'.$row->NOMUAT.'" title="Pilih">
    //                         <i class="bx bx-check" style="font-size: 14px;"></i>
    //                     </button>';
    //             $btn .= '<button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 deleteRentDgn"
    //                         data-id="'.$row->id.'" data-nomuat="'.$row->NOMUAT.'" title="Hapus">
    //                         <i class="bx bx-trash" style="font-size: 14px;"></i>
    //                     </button>';
    //             $btn .= '</div>';
    //             return $btn;
    //         })
    //         ->addColumn('total_formatted', function($row) {
    //             return 'Rp ' . number_format($row->GRAND, 0, ',', '.');
    //         })
    //         ->addColumn('harga_formatted', function($row) {
    //             return 'Rp ' . number_format($row->HARGA, 0, ',', '.');
    //         })
    //         ->addColumn('dc_formatted', function($row) {
    //             return 'Rp ' . number_format($row->DC, 0, ',', '.');
    //         })
    //         ->editColumn('TGLMUAT', function($row) {
    //             return $row->TGLMUAT ? date('d-m-Y', strtotime($row->TGLMUAT)) : '-';
    //         })
    //         ->editColumn('JUMLAH', function($row) {
    //             return number_format($row->JUMLAH, 0, ',', '.') . ' ' . $row->UNIT;
    //         })
    //         ->editColumn('DISC', function($row) {
    //             return $row->DISC ? $row->DISC . '%' : '-';
    //         })
    //         ->rawColumns(['action', 'total_formatted', 'harga_formatted', 'dc_formatted'])
    //         ->make(true);
    // }
