<?php

namespace App\Domains\Api\Auth\Controllers;

use App\Domains\Core\User\Models\User;
use App\Http\Controllers\APIController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends APIController
{
    // forget password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email:dns', 'exists:users,email,deleted_at,NULL']],[],['email'=> trans('cruds.api.email')]);

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $token = rand(1000, 9999);

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            DB::table('password_reset_tokens')
                ->updateOrInsert(
                    ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()],
                    ['email' => $request->email]
                );

            $subject = trans('emails.forgot_password_otp_mail_user.subject',[], $user->language);
            $expiretime = config('auth.passwords.users.otp_expire') . ' Minutes';
            $user->sendPasswordResetOtpNotification($user, $token, $subject, $expiretime);

            DB::commit();

            return $this->apiSuccess(trans('auth.messages.forgot_password.otp_sent'));
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    // Verify forget password OTP
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email:dns|exists:password_reset_tokens,email',
                'otp'   => 'required|numeric|min:4'
            ],[],[
                'email' => trans('cruds.api.email'),
                'otp' => trans('cruds.api.otp'),
            ]);


            $passwordReset = DB::table('password_reset_tokens')
                ->where('token', $request->otp)
                ->where('email', $request->email)
                ->latest()
                ->first();

            if (!$passwordReset) {
                return $this->apiError(trans('auth.messages.forgot_password.validation.invalid_otp'));
            }

            if (Carbon::parse($passwordReset->created_at)->addMinutes(config('auth.passwords.users.otp_expire'))->isPast()) {
                return $this->apiError(trans('auth.messages.forgot_password.validation.expire_otp'));
            }

            return $this->apiSuccess(['token' => encrypt($request->otp)], trans('auth.messages.forgot_password.validation.verified_otp'));
        } catch (\Exception $e) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'     => ['required'],
            'email'     => ['required', 'email:dns', 'exists:users,email,deleted_at,NULL'],
            'password'  => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/'],
            'confirmed_password' => ['required', 'string', 'same:password']
        ], [
            'password.regex' => trans('validation.password.regex',['attribute'=> trans('cruds.api.password')]),
        ],[
            'token' => trans('cruds.api.token'),
            'email' => trans('cruds.api.email'),
            'password' => trans('cruds.api.password'),
            'confirmed_password' => trans('cruds.api.confirmed_password'),
        ]);
        DB::beginTransaction();
        try {
            $token = decrypt($request->token);
            $passwordReset = DB::table('password_reset_tokens')->where('token', $token)
                ->where('email', $request->email)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$passwordReset) {
                return $this->apiError(trans('auth.messages.forgot_password.validation.invalid_token_email'));
            }

            $user = User::where('email', $passwordReset->email)->first();
            if (!$user) {
                return $this->apiError(trans('auth.messages.forgot_password.validation.email_not_found'));
            }

            if (Hash::check($request->password, $user->password)) {
                return $this->apiError(trans('auth.messages.forgot_password.validation.same_as_old_password'));
            }

            $user->password = bcrypt($request->password);
            $user->save();
            DB::table('password_reset_tokens')->where('email', $passwordReset->email)->delete();

            DB::commit();

            return $this->apiSuccess(trans('auth.messages.forgot_password.success_update'));
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return $this->apiError(trans('messages.error_message'));
        }
    }
}
