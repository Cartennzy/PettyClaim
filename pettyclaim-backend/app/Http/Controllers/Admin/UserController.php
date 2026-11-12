<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Tampilkan Daftar Pengguna Terurut berdasarkan Departemen & Role
     */
    public function index()
    {
        $users = User::orderBy('department', 'asc')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 1 WHEN role = 'manager' THEN 2 ELSE 3 END")
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.dashboard', compact('users'));
    }

    /**
     * Registrasi Akun Pengguna Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nip'          => ['nullable', 'string', 'max:50', 'unique:users,nip'],
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'department'   => ['required', 'string', 'max:100'],
            'password'     => ['required', 'string', 'min:6'],
            'role'         => ['required', 'in:karyawan,manager,admin'],
        ], [
            'name.required'       => 'Nama lengkap wajib diisi.',
            'email.required'      => 'Alamat email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email ini sudah terdaftar di sistem.',
            'nip.unique'          => 'NIP ini sudah terdaftar di sistem.',
            'department.required' => 'Divisi / Departemen wajib diisi.',
            'password.required'   => 'Kata sandi awal wajib diisi.',
            'password.min'        => 'Kata sandi minimal 6 karakter.',
            'role.required'       => 'Peran pengguna wajib dipilih.',
        ]);

        $rawPassword = $request->password;

        $user = User::create([
            'nip'            => $request->nip,
            'name'           => $request->name,
            'email'          => $request->email,
            'phone_number'   => $request->phone_number,
            'department'     => trim($request->department),
            'wallet_balance' => 0,
            'password'       => Hash::make($rawPassword),
            'role'           => $request->role,
        ]);

        try {
            $user->notify(new AccountCreatedNotification($rawPassword, $request->role));
        } catch (\Throwable $e) {
            Log::error('Error kirim email notifikasi user: ' . $e->getMessage());
        }

        $roleTitle = ucfirst($user->role);

        return redirect()->route('admin.dashboard')
            ->with('success', "Akun {$roleTitle} ({$user->name}) Divisi {$user->department} berhasil didaftarkan!")
            ->with('activeTab', 'users');
    }

    /**
     * Update Data Pengguna (Edit Divisi / Data User)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'department'   => ['required', 'string', 'max:100'],
            'nip'          => ['nullable', 'string', 'max:50', 'unique:users,nip,' . $user->id],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'role'         => ['required', 'in:karyawan,manager,admin'],
        ]);

        $user->update([
            'name'         => $request->name,
            'email'        => $request->email,
            'department'   => trim($request->department),
            'nip'          => $request->nip,
            'phone_number' => $request->phone_number,
            'role'         => $request->role,
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.")
            ->with('activeTab', 'users');
    }

    /**
     * Hapus Pengguna secara Permanen dengan Proteksi Akun Aktif
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang aktif digunakan.')
                ->with('activeTab', 'users');
        }

        $userName = $user->name;

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        return redirect()->route('admin.dashboard')
            ->with('success', "Akun pengguna {$userName} berhasil dihapus permanen.")
            ->with('activeTab', 'users');
    }
}