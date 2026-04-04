<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Consultas - ambos roles
    Route::get('/consultas', [ConsultaController::class, 'index'])->name('consultas.index');
    Route::get('/consultas/search', [ConsultaController::class, 'search'])->name('consultas.search');
    Route::get('/consultas/{consulta}', [ConsultaController::class, 'show'])->name('consultas.show');

    // Solo admin
    Route::middleware('role:admin')->group(function () {
        Route::post('/consultas/upload', [ConsultaController::class, 'upload'])->name('consultas.upload');
        Route::get('/consultas/{consulta}/process', [ConsultaController::class, 'process'])->name('consultas.process');
        Route::post('/consultas/{consulta}/process-next', [ConsultaController::class, 'processNext'])->name('consultas.processNext');
        Route::post('/consultas/{consulta}/pause', [ConsultaController::class, 'pause'])->name('consultas.pause');
        Route::get('/consultas/{consulta}/export', [ConsultaController::class, 'export'])->name('consultas.export');
        Route::get('/files', [ConsultaController::class, 'files'])->name('consultas.files');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
