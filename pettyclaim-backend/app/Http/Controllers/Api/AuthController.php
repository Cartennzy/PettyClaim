<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login Pengguna dari Flutter Mobile
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat email atau password salah.',
                'errors'  => [
                    'email'    => ['Alamat email atau password salah.'],
                    'password' => ['Alamat email atau password salah.'],
                ]
            ], 401);
        }

        // Generate Token Sanctum
        $token = $user->createToken('pettyclaim-mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'             => $user->id,
                'nip'            => (string) ($user->nip ?? $user->employee_id ?? $user->nik ?? '-'),
                'name'           => $user->name,
                'email'          => $user->email,
                'phone_number'   => (string) ($user->phone_number ?? $user->no_hp ?? $user->phone ?? '-'),
                'department'     => (string) ($user->department ?? $user->departemen ?? '-'),
                'role'           => $user->role,
                'wallet_balance' => (double) ($user->wallet_balance ?? 0.0),
            ]
        ], 200);
    }

    /**
     * Ambil Profil Pengguna via Query Param ?email=... atau Token Sanctum
     */
    public function getProfile(Request $request)
    {
        $user = null;

        if ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            $user = auth('sanctum')->user() ?? auth()->user();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $user->id,
                'nip'            => (string) ($user->nip ?? $user->employee_id ?? $user->nik ?? '-'),
                'name'           => $user->name,
                'email'          => $user->email,
                'phone_number'   => (string) ($user->phone_number ?? $user->no_hp ?? $user->phone ?? $user->whatsapp ?? '-'),
                // Inject nama asli db sebagai cadangan
                'no_hp'          => (string) ($user->no_hp ?? ''),
                'employee_id'    => (string) ($user->employee_id ?? ''),
                'department'     => (string) ($user->department ?? $user->departemen ?? '-'),
                'role'           => $user->role,
                'wallet_balance' => (double) ($user->wallet_balance ?? 0.0),
            ]
        ], 200);
    }

    /**
     * Ganti Password Pengguna dari Mobile
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'email'            => 'required|email',
            'old_password'     => 'required',
            'new_password'     => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama Anda tidak sesuai.',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ], 200);
    }
}