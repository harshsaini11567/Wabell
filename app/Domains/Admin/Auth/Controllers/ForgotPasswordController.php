<?php

namespace App\Domains\Admin\Auth\Controllers;

use App\Domains\Admin\Auth\Mail\ResetPasswordMail;
use App\Http\Controllers\Controller;
use App\Domains\Core\User\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    public function index()
    {
        return view('Auth::forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate(['email' => ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','exists:users,email,deleted_at,NULL']], getCommonValidationRuleMsgs());

        DB::beginTransaction();
        try{
            $user = User::where('email',$request->email)->first();
            if($user){
                $token = generateRandomString(64);
                $emailId = $request->email;

                $resetPasswordUrl = route('reset.password',['token'=>$token]);
                
                DB::table('password_reset_tokens')
                ->where('email', $emailId)
                ->delete();

                DB::table('password_reset_tokens')->insert([
                    'email' => $emailId,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);

                $subject = trans('emails.reset_password_mail_user.subject', [], $user['language']);
                Mail::to($emailId)->send(new ResetPasswordMail($user, $resetPasswordUrl, $subject));

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => __('passwords.sent')
                ]);

            }else{
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_email')
                ], 400);

            }

        }catch(\Exception $e){
            DB::rollBack();
            // dd($e);
            return response()->json([
                'success' => false,
                'message' => __('messages.error_message')
            ], 400);
        }
    }
}
