<?php

namespace App\Http\Controllers\Wedding\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingGuest;
use App\Models\WeddingRsvp;
use App\Models\WeddingWish;
use App\Support\AdminWhatsApp;

class DashboardController extends Controller
{
    public function index()
    {
        $months = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);

            return [
                'label' => $date->translatedFormat('M'),
                'total' => Wedding::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        });

        return view('wedding.admin.dashboard', [
            'stats' => [
                'customer' => User::where('role', User::ROLE_CUSTOMER)->count(),
                'undangan' => Wedding::count(),
                'terbit' => Wedding::where('status', Wedding::STATUS_PUBLISHED)->count(),
                'tamu' => WeddingGuest::count(),
                'hadir' => WeddingRsvp::where('attendance', WeddingRsvp::HADIR)->count(),
                'ucapan' => WeddingWish::count(),
                'pengajuan' => Wedding::whereIn('submission_status', [
                    Wedding::SUBMISSION_WAITING_CONFIRMATION,
                    Wedding::SUBMISSION_WAITING_PAYMENT,
                    Wedding::SUBMISSION_WAITING_VERIFICATION,
                ])->count(),
            ],
            'latestWeddings' => Wedding::with(['user', 'template'])->latest()->take(6)->get(),
            'latestSubmissions' => Wedding::with(['user', 'template'])
                ->whereNotIn('submission_status', [Wedding::SUBMISSION_DRAFT])
                ->latest('submitted_at')->take(5)->get(),
            'months' => $months,
            // Peringatan di dasbor bila nomor WhatsApp admin belum dikonfigurasi.
            'adminWhatsAppConfigured' => AdminWhatsApp::isConfigured(),
        ]);
    }
}
