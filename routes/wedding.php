<?php

use App\Http\Controllers\Wedding\AccountController;
use App\Http\Controllers\Wedding\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Wedding\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Wedding\Admin\PublicationController as AdminPublicationController;
use App\Http\Controllers\Wedding\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Wedding\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Wedding\Admin\WeddingController as AdminWeddingController;
use App\Http\Controllers\Wedding\AuthController;
use App\Http\Controllers\Wedding\BroadcastController;
use App\Http\Controllers\Wedding\CatalogController;
use App\Http\Controllers\Wedding\DashboardController;
use App\Http\Controllers\Wedding\GuestController;
use App\Http\Controllers\Wedding\InvitationController;
use App\Http\Controllers\Wedding\LandingController;
use App\Http\Controllers\Wedding\PublicInvitationController;
use App\Http\Controllers\Wedding\RsvpController;
use App\Http\Controllers\Wedding\WishController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Wedding — Markas Punokawan
|--------------------------------------------------------------------------
| Seluruh route modul berada di bawah prefix /wedding dan name prefix
| wedding. Route publik /wedding/{slug} ditempatkan paling akhir agar tidak
| menimpa route dashboard, admin, katalog, atau autentikasi.
|
| Tidak ada paket/langganan. Semua customer mendapat akses penuh. Publikasi
| undangan hanya dapat dilakukan oleh Super Admin setelah pembayaran manual
| dikonfirmasi.
*/

Route::prefix('wedding')->name('wedding.')->group(function () {
    // Halaman publik modul
    Route::get('/', LandingController::class)->name('home');
    Route::get('/templates', [CatalogController::class, 'index'])->name('templates.index');
    Route::get('/templates/{slug}', [CatalogController::class, 'show'])->name('templates.show');
    Route::get('/templates/{slug}/preview', [CatalogController::class, 'preview'])->name('templates.preview');
    Route::get('/templates/{slug}/gunakan', [CatalogController::class, 'use'])->name('templates.use');

    // Autentikasi modul. Jangan memakai /login atau /register root.
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1')->name('login.attempt');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.attempt');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    // Dashboard customer — akses penuh, tanpa paket.
    Route::middleware(['auth', 'role:customer'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
        Route::get('/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
        Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::get('/invitations/{wedding}', [InvitationController::class, 'show'])->name('invitations.show');
        Route::get('/invitations/{wedding}/edit', [InvitationController::class, 'edit'])->name('invitations.edit');
        Route::put('/invitations/{wedding}', [InvitationController::class, 'update'])->name('invitations.update');
        Route::post('/invitations/{wedding}/template', [InvitationController::class, 'changeTemplate'])->name('invitations.template');
        Route::get('/invitations/{wedding}/preview', [InvitationController::class, 'preview'])->name('invitations.preview');
        // Customer hanya MENGAJUKAN publikasi; tidak ada route publish untuk customer.
        Route::post('/invitations/{wedding}/submit-publication', [InvitationController::class, 'submitPublication'])->name('invitations.submit_publication');
        Route::post('/invitations/{wedding}/claim-payment', [InvitationController::class, 'claimPayment'])->name('invitations.claim_payment');
        Route::post('/invitations/{wedding}/duplicate', [InvitationController::class, 'duplicate'])->name('invitations.duplicate');
        Route::delete('/invitations/{wedding}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

        Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
        Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
        Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
        Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');
        Route::post('/guests/import', [GuestController::class, 'import'])->name('guests.import');
        Route::get('/guests/export', [GuestController::class, 'export'])->name('guests.export');
        Route::get('/guests/{guest}/link', [GuestController::class, 'link'])->name('guests.link');

        Route::get('/rsvp', [RsvpController::class, 'index'])->name('rsvp.index');
        Route::get('/wishes', [WishController::class, 'index'])->name('wishes.index');
        Route::patch('/wishes/{wish}', [WishController::class, 'moderate'])->name('wishes.moderate');
        Route::delete('/wishes/{wish}', [WishController::class, 'destroy'])->name('wishes.destroy');

        Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('/broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
        Route::get('/broadcast/{campaign}', [BroadcastController::class, 'show'])->name('broadcast.show');
        Route::put('/broadcast/{campaign}', [BroadcastController::class, 'update'])->name('broadcast.update');
        Route::post('/broadcast/{campaign}/share', [BroadcastController::class, 'share'])->name('broadcast.share');
        Route::delete('/broadcast/{campaign}', [BroadcastController::class, 'destroy'])->name('broadcast.destroy');

        Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AccountController::class, 'update'])->name('account.update');
        Route::put('/account/password', [AccountController::class, 'password'])->name('account.password');
    });

    // Dashboard Super Admin Wedding
    Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{user}', [AdminCustomerController::class, 'show'])->name('customers.show');

        Route::get('/templates', [AdminTemplateController::class, 'index'])->name('templates.index');
        Route::patch('/templates/{template}', [AdminTemplateController::class, 'update'])->name('templates.update');

        Route::get('/weddings', [AdminWeddingController::class, 'index'])->name('weddings.index');

        // Menu PENGAJUAN PUBLIKASI — hanya Super Admin.
        Route::get('/publications', [AdminPublicationController::class, 'index'])->name('publications.index');
        Route::get('/publications/{wedding}', [AdminPublicationController::class, 'show'])->name('publications.show');
        Route::get('/publications/{wedding}/preview', [AdminPublicationController::class, 'preview'])->name('publications.preview');
        Route::post('/publications/{wedding}/payment-instruction', [AdminPublicationController::class, 'paymentInstruction'])->name('publications.payment_instruction');
        Route::post('/publications/{wedding}/confirm-payment', [AdminPublicationController::class, 'confirmPayment'])->name('publications.confirm_payment');
        Route::post('/publications/{wedding}/revision', [AdminPublicationController::class, 'requestRevision'])->name('publications.revision');
        Route::post('/publications/{wedding}/publish', [AdminPublicationController::class, 'publish'])->name('publications.publish');
        Route::post('/publications/{wedding}/unpublish', [AdminPublicationController::class, 'unpublish'])->name('publications.unpublish');

        Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/whatsapp', [AdminSettingController::class, 'updateWhatsApp'])->name('settings.whatsapp');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });

    // Aksi publik undangan
    Route::post('/{slug}/rsvp', [PublicInvitationController::class, 'rsvp'])
        ->middleware('throttle:'.config('wedding.limits.rsvp_per_minute', 6).',1')
        ->name('rsvp.store')
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
    Route::post('/{slug}/ucapan', [PublicInvitationController::class, 'wish'])
        ->middleware('throttle:'.config('wedding.limits.wishes_per_minute', 4).',1')
        ->name('wishes.store')
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');

    // Undangan publik — HARUS paling akhir. Slug dibatasi dan dicek reserved.
    Route::get('/{slug}', [PublicInvitationController::class, 'show'])
        ->name('invitation.show')
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
});
