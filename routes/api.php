<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosnetController;

// Ruta para registrar una tarjeta
Route::post('/cards', [PosnetController::class, 'registerCard']);

// Ruta para procesar un pago
Route::post('/payments', [PosnetController::class, 'doPayment']);