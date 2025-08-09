<?php 
Route::resource('TipeKendaraan', App\Http\Controllers\TipeKendaraanController::class); 

Route::resource('MerekKendaraan', App\Http\Controllers\MerekKendaraanController::class); 

Route::resource('ModelKendaraan', App\Http\Controllers\ModelKendaraanController::class); 

Route::resource('TipeKendaraanCrud', App\Http\Controllers\TipeKendaraanCrudController::class); 
