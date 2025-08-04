<?php 
Route::resource('Kategori', App\Http\Controllers\KategoriController::class); 

Route::resource('Artikel', App\Http\Controllers\ArtikelController::class); 

Route::resource('TipeKendaraan', App\Http\Controllers\TipeKendaraanController::class); 

Route::resource('MerekKendaraan', App\Http\Controllers\MerekKendaraanController::class); 

Route::resource('ModelKendaraan', App\Http\Controllers\ModelKendaraanController::class); 

Route::resource('Dashboard', App\Http\Controllers\DashboardController::class); 
