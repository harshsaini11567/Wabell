<?php

use App\Domains\Api\Common\Controllers\ContentController;
use Illuminate\Support\Facades\Route;


Route::get('/privacy-policy', [ContentController::class, 'privacyPolicy']);  // PP
Route::get('/term-condition', [ContentController::class, 'termCondition']);  // T&C
