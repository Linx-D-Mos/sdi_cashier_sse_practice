<?php

use App\Http\Controllers\AlertaStreamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('api/alertas-stream', [AlertaStreamController::class, 'stream']);
