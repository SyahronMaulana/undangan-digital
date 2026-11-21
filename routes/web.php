<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Markas Punokawan — Platform Routes
|--------------------------------------------------------------------------
| Root hanya untuk halaman utama platform. Modul Wedding memiliki route
| group sendiri di routes/wedding.php dengan prefix /wedding.
*/

Route::view('/', 'platform.home')->name('home');

require __DIR__.'/wedding.php';
