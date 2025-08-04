<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Core\ModuleController;
use App\Http\Controllers\Core\SettingController;
use App\Http\Controllers\Core\MenuController;
use App\Http\Controllers\Core\UsersController;
use App\Http\Controllers\Core\GroupController;
use App\Http\Controllers\Core\ProfileController;
use App\Http\Controllers\Core\CategoriesController;
use App\Http\Controllers\Core\ContentController;
use App\Http\Controllers\Core\DocumentationController;
use App\Http\Controllers\Core\DatabaseController;
use App\Http\Controllers\Core\MediaController;
use App\Http\Controllers\Core\AuditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/registerSocial', [AuthController::class, 'registerSocial']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/info', [SettingController::class, 'info']);
Route::get('/docs', [DocumentationController::class, 'docs']);
Route::get('/docs/{content}', [DocumentationController::class, 'docsContent']);


Route::group(['prefix' => 'core', 'middleware' => ['auth:sanctum', 'updateLastActivity']], function () {
    Route::resource('/categories', CategoriesController::class);
    Route::resource('/content', ContentController::class);
    Route::resource('/modules', ModuleController::class);
    Route::resource('/setting', SettingController::class);
    Route::resource('/profile', ProfileController::class);
    Route::resource('/menus', MenuController::class);
    Route::resource('/users', UsersController::class);
    Route::resource('/groups', GroupController::class);
    Route::resource('/database', DatabaseController::class);
    Route::resource('/audit', AuditController::class);
});
Route::resource('/core/media', MediaController::class);
Route::group(['middleware' => 'auth:sanctum'], function () {
    include('modules.php');
});
