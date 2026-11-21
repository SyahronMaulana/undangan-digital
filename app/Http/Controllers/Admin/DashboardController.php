<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wedding;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'customer' => User::where('role', User::ROLE_CUSTOMER)->count(),
                'undangan' => Wedding::count(),
                'terbit' => Wedding::where('status', Wedding::STATUS_PUBLISHED)->count(),
                'pembayaran_menunggu' => Payment::where('status', 'menunggu')->count(),
            ],
        ]);
    }
}
