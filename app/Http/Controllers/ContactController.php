<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    /**
     * Tampilkan halaman hubungi kami
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Kirim pesan ke WhatsApp dan simpan backup di log lokal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'topic' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // 1. Simpan backup lokal ke file JSON di storage/app
        $backupFile = 'contact_messages.json';
        $existingMessages = [];
        
        if (Storage::disk('local')->exists($backupFile)) {
            $existingMessages = json_decode(Storage::disk('local')->get($backupFile), true) ?: [];
        }

        $newMessage = [
            'id' => uniqid(),
            'name' => $validated['name'],
            'whatsapp' => $validated['whatsapp'],
            'email' => $validated['email'],
            'topic' => $validated['topic'],
            'message' => $validated['message'],
            'created_at' => now()->toDateTimeString(),
        ];

        $existingMessages[] = $newMessage;
        Storage::disk('local')->put($backupFile, json_encode($existingMessages, JSON_PRETTY_PRINT));

        // 2. Format nomor HP tujuan untuk WhatsApp
        // Kita ambil contact_phone dari database (melalui PageContent)
        $phoneRecord = \App\Models\PageContent::where('section_key', 'contact_phone')->first();
        $targetPhone = $phoneRecord?->content_text ?? '085477689811';
        
        // Bersihkan karakter non-digit
        $phoneClean = preg_replace('/[^0-9]/', '', $targetPhone);
        
        // Ubah awalan 0 menjadi 62
        if (str_starts_with($phoneClean, '0')) {
            $phoneClean = '62' . substr($phoneClean, 1);
        }
        
        // Fallback jika kosong setelah pembersihan
        if (empty($phoneClean)) {
            $phoneClean = '6285477689811';
        }

        // 3. Buat template pesan WhatsApp
        $text = "*Hubungi Kami - Pondok Pesantren Nurul Khidmah*\n\n";
        $text .= "*Nama Lengkap*: " . $validated['name'] . "\n";
        $text .= "*No. WhatsApp*: " . $validated['whatsapp'] . "\n";
        $text .= "*Email*: " . $validated['email'] . "\n";
        $text .= "*Topik Pertanyaan*: " . $validated['topic'] . "\n\n";
        $text .= "*Pesan*:\n" . $validated['message'];

        $waUrl = "https://wa.me/" . $phoneClean . "?text=" . urlencode($text);

        // 4. Redirect ke WhatsApp
        return redirect()->away($waUrl);
    }
}
