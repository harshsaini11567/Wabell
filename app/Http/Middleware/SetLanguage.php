<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Facades\JWTAuth;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = JWTAuth::user();
        if ($user) {
            $newLocale = $user->language ?? 'en';

            $currentLocale = App::getLocale();
            if ($currentLocale != $newLocale && in_array($newLocale, ['en', 'ar'])) {
                App::setLocale($newLocale);
            }
        }
        else{
            // $newLocale = $request->query('language') ? $request->query('language') : ($request->input('language') ? $request->input('language') : 'en');
            $newLocale = $request->header('language') ?? $request->query('language') ?? ($request->input('language') ?? 'en');
            App::setLocale($newLocale);
        }
        return $next($request);
    }
}
