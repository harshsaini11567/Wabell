<?php

use App\Domains\Admin\Setting\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('settings/update', [SettingController::class, 'UpdateSiteSetting'])->name('settings.update');
Route::get('/settings/tutor-chat-status', [SettingController::class, 'toggleTutorChatStatus'])->name('settings.tutor_chat_status');