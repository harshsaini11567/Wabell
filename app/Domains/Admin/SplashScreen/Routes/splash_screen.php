<?php

use App\Domains\Admin\SplashScreen\Controllers\SplashScreenController;
use Illuminate\Support\Facades\Route;

Route::resource('splash-screens', SplashScreenController::class);
Route::post('splash-screens/sort', [SplashScreenController::class, 'sort'])->name('splash-screens.sort');