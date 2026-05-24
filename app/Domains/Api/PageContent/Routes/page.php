<?php

use App\Domains\Api\PageContent\Controllers\PageContentController;
use Illuminate\Support\Facades\Route;

// Static Page Content Routes
Route::get('pages/{slug}', [PageContentController::class, 'index']);

// Footer Page Content Routes
Route::get('footer', [PageContentController::class, 'getFooterData']);


