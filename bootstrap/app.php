<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['setLanguage'])->prefix('api')->name('api')->group(function () {
                require base_path('app/Domains/Api/Auth/Routes/auth.php');
                require base_path('app/Domains/Api/Common/Routes/content.php');
                require base_path('app/Domains/Api/PageContent/Routes/page.php');
                require base_path('app/Domains/Api/Common/Routes/common.php');
            });
            
            Route::middleware(['auth:api','setLanguage','jwt.verify'])->prefix('api')->name('api')->group(function () {
                require base_path('app/Domains/Api/Customer/Routes/customer.php');
                require base_path('app/Domains/Api/SubscriptionPlan/Routes/subscription_plan.php');
                require base_path('app/Domains/Api/Conversation/Routes/conversation.php');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\AuthGates::class,
            \App\Http\Middleware\RedirectIfInactive::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\AuthGates::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\SetLanguage::class,
            \App\Http\Middleware\TokenVersion::class
        ]);

        $middleware->alias([
            'PreventBackHistory' =>\App\Http\Middleware\PreventBackHistory::class,
            'AuthGates' =>\App\Http\Middleware\AuthGates::class,
            // 'checkUserStatus' => \App\Http\Middleware\CheckUserStatus::class,
            // 'userinactive' => \App\Http\Middleware\RedirectIfInactive::class,
            // 'check.device' => \App\Http\Middleware\LogoutUserFromOtherDevice::class,
            // 'role' => \App\Http\Middleware\CheckRole::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'setLanguage' =>  \App\Http\Middleware\SetLanguage::class,
            'jwt.verify' => \App\Http\Middleware\TokenVersion::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
