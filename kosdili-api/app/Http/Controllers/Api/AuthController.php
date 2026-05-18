<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'phone'    => 'nullable|string|max:20',
        'password' => 'required|string|min:6|confirmed',
        'role'     => 'required|in:owner,user',
    ]);

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'phone'    => $request->phone,
        'password' => Hash::make($request->password),
        'role'     => $request->role,
    ]);

    // Kirim email verifikasi
    event(new \Illuminate\Auth\Events\Registered($user));

    // ✅ TIDAK return token — user harus verifikasi dulu
    return response()->json([
        'status'  => 'success',
        'message' => 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi.',
        'email'   => $user->email, // ← kirim email agar frontend bisa simpan
    ], 201);
}

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // ✅ Cek apakah email sudah diverifikasi
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email belum diverifikasi. Silakan cek inbox Anda.',
                'action'  => 'verify_email',
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // Simpan user info lengkap termasuk phone & role
        return response()->json([
            'status' => 'success',
            'token'  => $token,
            'user'   => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role'  => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logout berhasil.']);
    }

    // ✅ Kirim ulang email verifikasi
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Email tidak ditemukan.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['status' => 'error', 'message' => 'Email sudah diverifikasi.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status'  => 'success',
            'message' => 'Email verifikasi telah dikirim ulang.',
        ]);
    }
}