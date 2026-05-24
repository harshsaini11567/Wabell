<?php

use App\Domains\Admin\Announcement\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::resource('announcements', AnnouncementController::class);