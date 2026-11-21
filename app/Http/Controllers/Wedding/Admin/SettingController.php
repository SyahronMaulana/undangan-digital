<?php

namespace App\Http\Controllers\Wedding\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeddingSetting;
use App\Rules\AdminWhatsAppNumber;
use App\Support\AdminWhatsApp;
use App\Support\Phone;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public const KEYS = [
        'support_whatsapp' => '+62 812 0000 0000',
        'support_email' => 'halo@markaspunokawan.test',
        'manual_payment_note' => 'Pembayaran dilakukan manual melalui transfer ke rekening Markas Punokawan. Hubungi Admin melalui chat WhatsApp untuk mendapatkan informasi rekening dan langkah selanjutnya. Admin akan memverifikasi pembayaran sebelum undangan dipublikasikan.',
        'wish_moderation' => 'post',
    ];

    /**
     * Nilai bawaan pengaturan. Nomor WhatsApp admin diambil dari konfigurasi
     * terpusat (WEDDING_ADMIN_WHATSAPP), bukan ditulis ulang di controller.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return ['admin_whatsapp' => (string) config('wedding.admin_whatsapp')] + self::KEYS;
    }

    public function edit()
    {
        $values = [];
        foreach (self::defaults() as $key => $default) {
            $values[$key] = WeddingSetting::get($key, $default);
        }

        return view('wedding.admin.settings', [
            'values' => $values,
            // Nomor WhatsApp admin aktif (satu sumber: WeddingSetting → config).
            'adminWhatsApp' => AdminWhatsApp::number(),
            'adminWhatsAppDisplay' => AdminWhatsApp::display(),
            'adminWhatsAppConfigured' => AdminWhatsApp::isConfigured(),
        ]);
    }

    /**
     * Simpan nomor WhatsApp admin. Nomor dinormalisasi ke format internasional
     * tanpa "+", spasi, atau tanda hubung (mis. 085155269940 → 6285155269940).
     * Nomor yang tidak lolos validasi tidak disimpan.
     */
    public function updateWhatsApp(Request $request)
    {
        $data = $request->validate([
            'admin_whatsapp' => ['required', 'string', 'max:30', new AdminWhatsAppNumber],
        ]);

        $normalized = Phone::normalize($data['admin_whatsapp']);
        // updateOrCreate → hanya ada satu baris untuk key admin_whatsapp.
        WeddingSetting::put('admin_whatsapp', ltrim((string) $normalized, '+'));

        return redirect()
            ->route('wedding.admin.settings.edit')
            ->with('status', 'Nomor WhatsApp admin berhasil diperbarui.');
    }

    /**
     * Pengaturan modul Wedding (dukungan, catatan pembayaran, moderasi ucapan).
     * Nomor WhatsApp admin TIDAK di sini agar hanya ada satu tempat mengubahnya.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'support_whatsapp' => ['required', 'string', 'max:30'],
            'support_email' => ['required', 'email', 'max:120'],
            'manual_payment_note' => ['required', 'string', 'max:2000'],
            'wish_moderation' => ['required', 'in:post,pre'],
        ]);

        foreach ($data as $key => $value) {
            WeddingSetting::put($key, $value);
        }

        return back()->with('status', 'Pengaturan modul Wedding disimpan.');
    }
}
