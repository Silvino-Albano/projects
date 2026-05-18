<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function send(string $phone, string $message): bool
    {
        // Pakai Fonnte (https://fonnte.com) — murah, support Indonesia & Timor
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target'  => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }
}