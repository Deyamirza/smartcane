<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\RealtimeController;

// Auth Routes
Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::post('/login', [WebController::class, 'login']);
Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::post('/register', [WebController::class, 'register']);
Route::post('/logout', [WebController::class, 'logout'])->name('logout');

// Realtime Stream (EventSource SSE)
Route::get('/realtime/stream', [RealtimeController::class, 'stream'])->name('realtime.stream');

// Device API endpoint (Exempt from CSRF in bootstrap/app.php)
Route::post('/api/log-data', [ApiController::class, 'logData'])->name('api.log_data');

// Protected Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');
    Route::get('/riwayat', [WebController::class, 'riwayat'])->name('riwayat');
    Route::delete('/riwayat/{id}', [WebController::class, 'deleteLog'])->name('riwayat.delete');
    Route::post('/riwayat/clear', [WebController::class, 'clearLogs'])->name('riwayat.clear');
    
    Route::get('/sos', [WebController::class, 'sos'])->name('sos');
    Route::post('/sos/resolve/{id}', [WebController::class, 'resolveSos'])->name('sos.resolve');
    Route::delete('/sos/{id}', [WebController::class, 'deleteSos'])->name('sos.delete');
    Route::post('/sos/clear', [WebController::class, 'clearSos'])->name('sos.clear');
    
    Route::get('/pengaturan', [WebController::class, 'pengaturan'])->name('pengaturan');
    Route::post('/pengaturan/update', [WebController::class, 'updatePengaturan'])->name('pengaturan.update');
});
