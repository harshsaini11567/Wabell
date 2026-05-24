<?php

use App\Domains\Api\SubscriptionPlan\Controllers\SubscriptionPlanController;
use App\Domains\Api\SubscriptionPlan\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('plan-list', [SubscriptionPlanController::class, 'subscriptionList']);
Route::post('subscription-store', [SubscriptionPlanController::class, 'subscriptionStore']);
Route::post('cancel-plan/{id}',[SubscriptionPlanController::class,'cancelPlan']);
Route::get('history-list', [SubscriptionPlanController::class, 'historyList']);
Route::post('hyperpay/checkout-id', [SubscriptionPlanController::class, 'getCheckoutId']);
Route::post('hyperpay/status/{checkoutId}', [SubscriptionPlanController::class, 'getPaymentStatus']);

Route::post('ios/verify-receipt', [SubscriptionPlanController::class, 'verifyApplePurchase']);
Route::post('webhook/apple', [WebhookController::class, 'handle']);