<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    // ✅ User klik link di email → verifikasi & redirect ke frontend
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals(
            sha1($user->getEmailForVerification()),
            $hash
        )) {
            return redirect(env('FRONTEND_URL') . '/verify-failed');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_URL') . '/login?verified=already');
        }

        $user->markEmailAsVerified();

        // Redirect ke frontend login dengan pesan sukses
        return redirect(env('FRONTEND_URL') . '/login?verified=success');
    }
}