<?php

use App\Domains\Api\Common\Controllers\CommonController;
use App\Domains\Api\Common\Controllers\ProfileController;
use App\Domains\Api\Common\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Content
Route::get('/faq', [CommonController::class, 'faq']);  // FAQ
Route::get('/about-us', [CommonController::class, 'aboutUs']);  // about us


Route::middleware(['auth:api','jwt.verify'])->group(function () {
    
    Route::get('dashboard', [CommonController::class, 'dashboard']);
    Route::get('/requested-users/search', [CommonController::class, 'searchRequestedUser']);
    Route::post('/user/availability', [CommonController::class, 'updateAvailability']);
    Route::get('/specialties/search', [CommonController::class, 'searchSpecialty']);
    Route::get('user/specialties', [CommonController::class, 'userSpecialty']);
    Route::post('change-language', [CommonController::class, 'changeUserLanguage']);

    // Review & Rating
    Route::get('/reviews-list', [CommonController::class, 'reviewList']);
    Route::post('/reviews', [CommonController::class, 'storeReview']);
    Route::put('/reviews/{id}', [CommonController::class, 'updateReview']);
    Route::delete('/reviews/{id}', [CommonController::class, 'deleteReview']);

    // Profile
    Route::get('profile', [ProfileController::class, 'profile']);
    Route::post('update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('send-otp-mobile-number', [ProfileController::class, 'updateMobileNumber']);
    Route::post('verify-otp-mobile-number', [ProfileController::class, 'checkOTPUpdateMobileNumber']);

    // Notification
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/unread-notifications', [NotificationController::class, 'unreadList']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);

    // Delete Account
    Route::delete('/user/account/{id}', [CommonController::class, 'deleteAccount']);

    Route::get('/user', [CommonController::class, 'getAuthenticatedUser']);

});