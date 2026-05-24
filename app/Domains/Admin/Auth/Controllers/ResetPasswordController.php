<?php

namespace App\Domains\Admin\Auth\Controllers;

use App\Domains\Core\User\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    public function index(Request $request)
    {
        return view('Auth::reset-password')->with(['token' =>$request->token]);
    }

    // Reset the given user's password.
    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', 'confirmed'],
            'password_confirmation' => 'required|string|min:8',

        ], getCommonValidationRuleMsgs());

        DB::beginTransaction();
        try{
            $updatePassword = DB::table('password_reset_tokens')->where(['token' => $request->token])->first();
            if(!$updatePassword){
                return response()->json([
                    'success' => false,
                    'message' => __('passwords.token')
                ], 400);
            }else{
                $email_id = $updatePassword->email;
                $retriveUser = User::where('email',$email_id)->first();
                if($retriveUser->user_status == 'active'){
                    if (Hash::check($request->password, $retriveUser->password)) {
                        return response()->json([
                            'success' => false,
                            'message' => __('passwords.same_as_old_password'),
                        ], 400);
                    }
                    User::where('email', $email_id)->update(['password' => Hash::make($request->password)]);

                    DB::table('password_reset_tokens')->where(['email'=> $email_id])->delete();

                    session()->flash('success', __('passwords.reset'));

                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('login')
                    ]);
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => __('passwords.suspened')
                    ], 400);
                }

            }
        }catch(\Exception $e){
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('messages.error_message')
            ], 400);
        }
    }
    


}
