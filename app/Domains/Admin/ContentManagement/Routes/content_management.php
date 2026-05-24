<?php

use App\Domains\Admin\ContentManagement\Controllers\ContentManagementController;
use App\Domains\Admin\ContentManagement\Controllers\WebFaqController;
use Illuminate\Support\Facades\Route;

Route::get('pages/{page}', [ContentManagementController::class, 'index'])->name('pages.index');
Route::post('comtent-management', [ContentManagementController::class, 'updatepPageContent'])->name('comtent-management.post');

Route::resource('web-faqs', WebFaqController::class);