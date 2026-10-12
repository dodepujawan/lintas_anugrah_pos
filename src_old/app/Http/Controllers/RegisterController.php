<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Driver;
use App\Models\Expedisi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function register()
    {
        return view('register.register');
    }

    public function actionregister(Request $request)
    {
        $result = [];

        // Start database transaction
        DB::beginTransaction();

        try {
            // Validate the request data
            $validatedData = $request->validate([
                'email' => 'required|email|unique:users',
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:6',
                'role' => 'required|string|max:255',
                'area_id' => 'required',
                'area_name' => 'required|string',
            ]);

            $roleMapping = [
                'AD' => 'admin',
                'ST' => 'staff',
                'CS' => 'customer',
                'DV' => 'driver',
            ];

            $role = $request->role;

            // Konversi nilai role menggunakan mapping, apabila tidak ada ubah nilai jadi guest
            $roleName = $roleMapping[$role] ?? 'customer';

            // Buat request untuk generate_user_id
            $generateRequest = new Request(['role' => $role]);

            // Panggil metode generateUserId untuk mendapatkan user_id
            $userIdResponse = $this->generate_user_id($generateRequest);
            $userId = $userIdResponse->getData()->user_id; // Mengambil user_id dari response

            $user = User::create([
                'user_id' => $userId,
                'email' => $request->email,
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'role_old' => $roleName,
                'area_id' => $request->area_id,
                'area_name' => $request->area_name,
            ]);

            // 🔥 WAJIB: assign ke Spatie
            $user->assignRole($roleName);

            // Jika role adalah driver (DV), simpan data ke tabel driver
            if ($role === 'DV') {
                // Import Carbon di atas class jika belum
                $lastKode = Driver::lockForUpdate()
                    ->orderByRaw('CAST(KODE AS UNSIGNED) DESC')
                    ->value('KODE');

                $next = ((int) $lastKode) + 1;

                // SIMPAN ANGKA MURNI
                $kodeDriver = (string) $next;

                Driver::create([
                    'user_id' => $userId,
                    'KODE' => $kodeDriver,
                    'NAMA' => $request->name,
                    'ALAMAT' => '0',
                    'PHONE' => '0',
                    'MULAI' => Carbon::now(),
                ]);
            }

            DB::commit();
            $result['pesan'] = 'Register Berhasil. Akun Anda sudah Aktif.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Rollback the transaction in case of validation error
            DB::rollback();
            $result['pesan'] = 'Validation Error: ' . implode(', ', Arr::flatten($e->errors()));
        } catch (\Exception $e) {
            // Rollback the transaction in case of general error
            DB::rollback();
            $result['pesan'] = 'Error: ' . $e->getMessage();
        }
        return response()->json($result);
    }

    public function editregister(){
            // Fetch user data
            $user = User::find(session('id')); // Assuming session has user id
            return view('register.editregister', compact('user'));
    }

    // UNTUK USER UPDATE SENDIRI
    public function updateregister(Request $request)
    {
        $result = [];
        DB::beginTransaction();
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'email' => 'required|email',
                'name' => 'required|string|max:255',
                'password' => 'nullable|string|min:8',
                'roles' => 'required|string|max:255',
            ]);

            $user = User::find(session('id'));

            // Update user details
            $user->email = $request->email;
            $user->name = $request->name;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $user->role_old = $request->roles;
            $user->save();
            // 🔥 update role Spatie
            $user->syncRoles([$request->roles]);

            DB::commit();
            $result['pesan'] = 'Update Berhasil.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            $result['pesan'] = 'Validation Error: ' . implode(', ', Arr::flatten($e->errors()));
        } catch (\Exception $e) {
            DB::rollback();
            $result['pesan'] = 'Error: ' . $e->getMessage();
        }
        return response()->json($result);
    }

    public function listregister()
    {
        return view('register.listregister');
    }

    public function filter_register(Request $request){
        if (!Auth::check()) {
            return response()->json(['message' => 'Silakan login terlebih dahulu'], 401);
        }

        $user = Auth::user();

        if ($user->role_old !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Build query dasar
        $query = DB::table('users')
            ->select([
                'users.id',
                'users.user_id',
                'users.email',
                'users.name',
                'users.role_old',
                DB::raw('DATE(users.created_at) as created_at')
            ]);

        // Tambahan filter custom untuk daterange dan text
        return DataTables::of($query)

            // Filter global custom (searchText)
            ->filter(function ($q) use ($request) {
                if ($request->filled('searchText')) {
                    $search = $request->searchText;
                    $q->where(function($sub) use ($search) {
                        $sub->where('users.email', 'like', "%$search%")
                            ->orWhere('users.name', 'like', "%$search%")
                            ->orWhere('users.user_id', 'like', "%$search%")
                            ->orWhere('users.role_old', 'like', "%$search%");
                    });
                }

                if ($request->filled('startDate')) {
                    $q->whereDate('users.created_at', '>=', $request->startDate);
                }

                if ($request->filled('endDate')) {
                    $q->whereDate('users.created_at', '<=', $request->endDate);
                }
            })

            ->make(true);
    }

    public function edit_list_register($id){
        // Fetch user data
        $user = User::find($id);// Assuming session has user id
        return response()->json($user);
    }

    public function select_list_register_staff($id){
        // Cari berdasarkan kolom user_id
        $user = User::where('user_id', $id)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function update_list_register(Request $request){
        $result = [];
        DB::beginTransaction();

        try {
            // ================= VALIDASI =================
            $request->validate([
                'email' => 'required|email',
                'name' => 'required|string|max:255',
                'password' => 'nullable|string|min:8',
                'roles_list_reg' => 'required|string|max:255',
                'area_id_reg_edit' => 'required',
                'area_name_reg_edit' => 'required|string',
            ]);

            $user = User::where('user_id', $request->input('id'))->first();

            if (!$user) {
                return response()->json(['pesan' => 'User not found.'], 404);
            }

            // ================= UPDATE DATA DASAR =================
            $user->email = $request->email;
            $user->name  = $request->name;

            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            // ================= ROLE SETUP =================
            $roleMapping = [
                'AD' => 'admin',
                'ST' => 'staff',
                'CS' => 'customer',
                'DV' => 'driver',
            ];

            $roleLama = strtolower(trim($user->role_old));
            $roleCode = $request->roles_list_reg;
            $roleBaru = $roleMapping[$roleCode] ?? 'customer';

            // ================= BLOK DRIVER → NON DRIVER =================
            $driverData = null;
            if ($roleLama === 'driver' && $roleBaru !== 'driver') {

                $driverData = Driver::where('user_id', $user->user_id)->first();

                if ($driverData) {
                    $kodeDriver = (int) $driverData->KODE;

                    $dipakai = Expedisi::whereRaw('CAST(DRIVER AS UNSIGNED) = ?', [$kodeDriver])
                        ->orWhereRaw('CAST(DRIVER2 AS UNSIGNED) = ?', [$kodeDriver])
                        ->exists();

                    // ❌ masih dipakai → TOLAK
                    if ($dipakai) {
                        DB::rollBack();
                        return response()->json([
                            'pesan' => 'Role driver masih aktif dan digunakan di data expedisi. Tidak dapat diubah.'
                        ], 422);
                    }
                    // ✅ TIDAK dipakai → LANJUT (nanti dihapus)
                }
            }
            // ================= UPDATE ROLE & USER_ID =================
            if ($user->role_old !== $roleBaru) {
                $oldUserId = $user->user_id;
                // generate user_id baru
                $generateRequest = new Request(['role' => $roleCode]);
                $userIdResponse  = $this->generate_user_id($generateRequest);
                $newUserId       = $userIdResponse->getData()->user_id;

                $user->user_id = $newUserId;
                $user->role_old   = $roleBaru;

                // ============ DRIVER → NON DRIVER (HAPUS DATA DRIVER) ============
                if ($roleLama === 'driver' && $roleBaru !== 'driver' && $driverData) {
                    Driver::where('user_id', $oldUserId)->delete();
                }

                // ============ NON DRIVER → DRIVER (INSERT DRIVER) ============
                if ($roleLama !== 'driver' && $roleBaru === 'driver') {

                    // generate KODE driver (angka murni)
                    $lastKode = Driver::lockForUpdate()
                        ->orderByRaw('CAST(KODE AS UNSIGNED) DESC')
                        ->value('KODE');

                    $nextKode = $lastKode ? ((int) $lastKode) + 1 : 1;

                    Driver::create([
                        'user_id' => $newUserId,
                        'KODE'    => (string) $nextKode,
                        'NAMA'    => $request->name,
                        'ALAMAT'  => '0',
                        'PHONE'   => '0',
                        'MULAI'   => Carbon::now(),
                    ]);
                }
            }

            // Update Area
            $user->area_id = $request->area_id_reg_edit;
            $user->area_name = $request->area_name_reg_edit;

            // ================= SAVE =================
            $user->save();
            $user->syncRoles([$roleBaru]);
            DB::commit();

            return response()->json([
                'pesan' => 'Update Berhasil.',
                'user_id_baru' => $user->user_id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'pesan' => 'Validation Error: ' . implode(', ', Arr::flatten($e->errors()))
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'pesan' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_list_register($id)
    {
        DB::beginTransaction();

        try {
            $user = User::where('user_id', $id)->first();

            if (!$user) {
                DB::rollBack();

                return response()->json(['error' => 'User tidak ditemukan'], 404);
            }

            // ================= JIKA ROLE DRIVER =================
            if (strtolower($user->role_old) === 'driver') {

                $driver = Driver::where('user_id', $user->user_id)->first();

                if ($driver) {
                    $kodeDriver = (int) $driver->KODE;

                    $dipakai = Expedisi::whereRaw('CAST(DRIVER AS UNSIGNED) = ?', [$kodeDriver])
                        ->orWhereRaw('CAST(DRIVER2 AS UNSIGNED) = ?', [$kodeDriver])
                        ->exists();

                    // ❌ DRIVER MASIH DIPAKAI
                    if ($dipakai) {
                        DB::rollBack();
                        return response()->json([
                            'error' => 'Driver masih digunakan di data expedisi. Tidak dapat dihapus.'
                        ], 422);
                    }

                    // ✅ AMAN → HAPUS DATA DRIVER
                    $driver->delete();
                }
            }

            // ================= HAPUS ROLE SPATIE =================
            DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('model_type', User::class)
                ->delete();

            // ================= HAPUS USER =================
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => 'User berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fungsi Calback id_user
    public function generate_user_id(Request $request){
        $role = $request->input('role');

        $lastUser = DB::table('users')
            ->where('user_id', 'LIKE', $role . '%')
            ->orderBy('user_id', 'desc')
            ->first();

        if ($lastUser) {
            // 1. Ambil Data user_id dari Objek, 2.strlen menghitung panjang string, substr memotong string berarti disini dipotong 2 karena nilai strlen role =2 _> substr('AD0005', 2) maka didapat nilai 0005. lalu int mendapat nilai integer disini berarti bernilai 5
            $lastNumber = (int) substr($lastUser->user_id, strlen($role));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $newUserId = $role . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return response()->json(['user_id' => $newUserId]);
    }

}
