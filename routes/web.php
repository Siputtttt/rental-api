<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome_sximo');
});

Route::get('/login/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/login/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
