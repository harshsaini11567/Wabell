<?php

use App\Domains\Api\Auth\Controllers\ForgotPasswordController;
use App\Domains\Api\Auth\Controllers\LoginController;
use App\Domains\Api\Auth\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('register/cities', [RegisterController::class, 'getCities']);
Route::get('register/neighborhoods/{cityId}', [RegisterController::class, 'getNeighborhoods']);

Route::post('request-specialty', [RegisterController::class, 'storeRequestSpecialty']);

Route::post('register', [RegisterController::class, 'register']);

Route::post('login', [LoginController::class, 'login']);


Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);

Route::post('password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);

Route::post('password/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::post('logout', [LoginController::class, 'logout'])->middleware(['auth:api']);

Route::post('refresh-token', [LoginController::class, 'refreshToken']);

Route::get('register/specialties', [RegisterController::class, 'searchSpecialty']);

Route::get('welcome-screens', [RegisterController::class, 'splashRecord']);

Route::get('welcome-video', [RegisterController::class, 'welcomeVideo']);