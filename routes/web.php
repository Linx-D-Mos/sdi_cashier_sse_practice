<?php

use App\Http\Controllers\AlertaStreamController;
use App\Models\Alert;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('api/alertas-stream', [AlertaStreamController::class, 'stream']);

Route::get('/api/crear-alerta-test', function () {
    // Insertamos una nueva fila en PostgreSQL
    $alerta = Alert::create([
        'message' => '¡Nueva venta procesada! Caja #1 recibió un pago de $150.00',
        'tipo'    => 'success'
    ]);

    return response()->json([
        'status' => 'Alerta inyectada en PostgreSQL',
        'data'   => $alerta
    ]);
});
