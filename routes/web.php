<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Api\SubscriptionPlan\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return redirect('login');
});

Route::get('/phpinfo', function () {
    phpinfo();
});


/* Route::middleware(['PreventBackHistory', 'guest'])->group(function () {
    require base_path('app/Domains/Admin/Auth/Routes/auth.php');
}); */

// Auth Routes
require base_path('app/Domains/Admin/Auth/Routes/auth.php');

Route::middleware(['auth', 'PreventBackHistory'])
->group(function () {
    // Add more route files as needed
    require base_path('app/Domains/Admin/Dashboard/Routes/dashboard.php');
    require base_path('app/Domains/Admin/City/Routes/city.php');
    require base_path('app/Domains/Admin/Role/Routes/role.php');
    require base_path('app/Domains/Admin/Specialty/Routes/specialty.php');
    require base_path('app/Domains/Admin/Setting/Routes/setting.php');
    require base_path('app/Domains/Admin/User/Routes/user.php');
    require base_path('app/Domains/Admin/Faq/Routes/faq.php');
    require base_path('app/Domains/Admin/MasterFaq/Routes/master_faq.php');
    require base_path('app/Domains/Admin/SplashScreen/Routes/splash_screen.php');
    require base_path('app/Domains/Admin/SubscriptionPlan/Routes/subscription_plan.php');
    require base_path('app/Domains/Admin/Announcement/Routes/announcement.php');
    require base_path('app/Domains/Admin/ContentManagement/Routes/content_management.php');
});

Route::post('hyperpay/webhook', [SubscriptionPlanController::class, 'webhook'])->name('hyperpay.webhook');
Route::get('/renew-subscriptions', function () {
    try {
        Artisan::call('app:renew-subscriptions');
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});