<?php

use App\Domains\Admin\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::get('heatmap-data', [DashboardController::class, 'getHeatmapData']);
// Profile 
Route::get('profile', [DashboardController::class, 'showProfile'])->name('show.profile');
Route::post('profile', [DashboardController::class, 'updateProfile'])->name('update.profile');
Route::post('remove-profile-image', [DashboardController::class, 'removeProfileImage'])->name('remove.profile-image');

Route::post('change-password', [DashboardController::class, 'updateChangePassword'])->name('update.change.password');