<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Http\Controllers\ReportController;

// Login page (guests only)
Route::get('/login', Login::class)
    ->middleware('guest')
    ->name('login');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

// POS (authenticated only)
Route::get('/', function () {
    return view('pos');
})->middleware('auth');

// Report download (admin + super_admin only)
Route::get('/report/download', [ReportController::class, 'download'])
    ->middleware('auth')
    ->name('report.download');
