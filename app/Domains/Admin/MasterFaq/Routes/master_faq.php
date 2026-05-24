<?php

use App\Domains\Admin\MasterFaq\Controllers\MasterFaqController;
use Illuminate\Support\Facades\Route;

Route::resource('master-faqs', MasterFaqController::class);