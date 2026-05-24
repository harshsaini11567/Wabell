<?php

use App\Domains\Admin\Faq\Controllers\FaqController;
use Illuminate\Support\Facades\Route;

Route::resource('faqs', FaqController::class);