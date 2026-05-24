<?php

use App\Domains\Admin\Auth\Controllers\ForgotPasswordController;
use App\Domains\Admin\Auth\Controllers\LoginController;
use App\Domains\Admin\Auth\Controllers\PaymentController;
use App\Domains\Admin\Auth\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['PreventBackHistory', 'guest']], function () {
    Route::get('login', [LoginController::class, 'login'])->name('login');
    Route::post('login', [LoginController::class, 'submitLogin'])->name('login.submit');

    Route::get('forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot.password');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forgot.password.submit');

    Route::get('password/reset-password', [ResetPasswordController::class, 'index'])->name('reset.password');
    Route::post('password/reset-password', [ResetPasswordController::class, 'reset'])->name('reset-new-password');


});

// Hyper Payment Form
Route::get('hyperpay/payment/form', [PaymentController::class, 'paymentForm'])->name('hyperpay.payment.form');
Route::get('hyperpay/callback', [PaymentController::class, 'paymentCallback'])->name('hyperpay.callback');

Route::get('logout', [LoginController::class, 'logout'])->name('auth.logout')->middleware(['auth', 'PreventBackHistory']);