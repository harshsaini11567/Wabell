<?php

namespace App\Domains\Admin\Auth\Controllers;

use App\Domains\Admin\Auth\Requests\LoginRequest;
use App\Domains\Core\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(){
        return view('Auth::login');
    }

    public function submitLogin(LoginRequest $request){
        $remember_me = !is_null($request->remember_me) ? true : false;

        $credentialsOnly = $request->only('email', 'password');
        // dd($credentialsOnly);
        if (Auth::attempt($credentialsOnly, $remember_me))
        {
            $user = Auth::user();         
            // restrict to do login to client admin when no active subscription and no trial subscription
            if(!$user->is_super_admin && !$user->is_admin){ 
                Auth::guard('web')->logout();
                return response()->json([
                    'success' => false,
                    'message' => __('auth.unauthorize')
                ], 400);
            }
            // Flash messages after login
            session()->flash('success', __('messages.login_success'));

            return response()->json([
                'success' => true,
                'redirect_url' => route('admin.dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.wrong_credentials')
        ], 400);
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return response()->json([
                'success' => true,
                'redirect_url' => route('login'),
        ]);
    }
}
