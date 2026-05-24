<?php

use App\Domains\Admin\SubscriptionPlan\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::resource('subscription-plans', SubscriptionPlanController::class);
Route::post('subscription-plans/change-status', [SubscriptionPlanController::class, "changeStatus"])->name('subscriptions.status');
Route::get('transaction', [SubscriptionPlanController::class, 'listTransaction'])
    ->name('transactions.list');
Route::get('transaction/{id}', [SubscriptionPlanController::class, 'showTransaction'])
    ->name('transactions.show');