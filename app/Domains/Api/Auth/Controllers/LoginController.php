<?php

namespace App\Domains\Api\Auth\Controllers;

use App\Domains\Admin\User\Resource\UserResource;
use App\Domains\Api\Auth\Requests\LoginRequest;
use App\Domains\Core\User\Models\User;
use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use App\Domains\Core\Conversation\Models\Message;

class LoginController extends APIController
{
    public function login(LoginRequest $request){
        try {
            $user_login = $request->user_login;
            $loginType = filter_var($user_login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            
            if(in_array($request->login_type, ['google', 'facebook', 'apple'])){
                $loginUser = User::where('login_type', $request->login_type)
                ->where('social_user_id', $request->social_user_id)
                ->first();
                if(!$loginUser){
                    return $this->apiError(trans('messages.social_user_not_found'));
                }

                $token = JWTAuth::fromUser($loginUser);
            } else {
                $credentials = [$loginType => $user_login, 'password' => $request->password];            
                $token = JWTAuth::attempt($credentials);
                if(!$token){
                    return $this->apiError(trans('messages.wrong_credentials'));
                }
                $loginUser = JWTAuth::setToken($token)->toUser();
            }

            $role_id = $loginUser->roles->first()->id;
            $roleTypes = $loginUser->roles()->pluck('role_type')->toArray();
            if (!in_array('app', $roleTypes)) {
                return $this->apiError(trans('messages.access_denied'));
            }

            // if($role_id == config('constant.roles.master') && !$loginUser->is_approved){
            //     return $this->apiError(trans('messages.account_approval'));
            // }

            // if($loginUser->is_ban){
            //     return $this->apiError(trans('messages.account_ban'));
            // }

            if($loginUser->user_status != 'active'){
                return $this->apiError(trans('messages.account_deactivate'));
            }

            $loginUser->increment('token_version');
            $customClaims = ['token_version' => $loginUser->token_version];
            $token = JWTAuth::claims($customClaims)->fromUser($loginUser);

            $user = User::find($loginUser->id);
            $user->update([
                'device_token'  => $request->header('Device-Token'),
                'language'  => $request->input('language'),
            ]);
            $user->load('roles');
            $userResource = new UserResource($user);
            $userData = $userResource->toArray(request());
            $userData['profile_image'] = $user->profile_image_url;
            $hasUnread = Message::whereHas('conversation.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->exists();
            $roleType = $user->roles->first()->name_en ?? null;
            // dd($roleType);
            $logo = null;
            if ($roleType === 'Learner') {
                $logo = getSetting('student_logo');
            } elseif ($roleType === 'Master') {
                $logo = getSetting('master_logo');
            }

            $profileCompletion = [];
            $profileCompleted  = false;
            if ($roleType === 'Master') {
                $profileCompletion = [
                    'date_of_birth'         => !empty($user->date_of_birth),
                    'certificate' => $user->uploads()->where('type', 'certificate_file')->exists(),
                    'specialty'   => $user->specialties()->exists(),
                ];

                // Add master profile_completed key
                $profileCompleted = 
                    $profileCompletion['date_of_birth'] && 
                    $profileCompletion['certificate'] && 
                    $profileCompletion['specialty'];
            }

            return $this->apiSuccess(array_merge([
                'access_token' => $token, 
                'user' => $userData,
                'is_read' => !$hasUnread, 
                'user_logo' => $logo,
            ], $roleType === 'Master' ? [
                'complete_profile_status' => $profileCompleted,
                'complete_profile_fields_status' => $profileCompletion,
            ] : []), trans('messages.login_success'));
        } catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function logout(Request $request)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return $this->apiError(trans('messages.user_not_authenticated'), 401);
            }
            $token = JWTAuth::getToken();
            $authUser = JWTAuth::toUser($token);
            if ($authUser) {
                $authUser->device_token = null;
                $authUser->save();
            }
            JWTAuth::invalidate($token);
            return $this->apiSuccess([],trans('messages.logout_success'));
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // dd($e);
            return $this->apiError(trans('messages.token_invalid'), 401);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');
            if (!$refreshToken || !str_contains($refreshToken, '.')) {
                return $this->apiError(trans('messages.invalid_refresh_token'));
            }
            [$token, $expiry] = explode('.', $refreshToken, 2);

            if (now()->timestamp > (int) $expiry) {
                return $this->apiError(trans('messages.token_expired'), 401);
            } 
            $hashedToken = hash('sha256', $refreshToken);

            $user = User::where('refresh_token', $hashedToken)->first();

            if (!$user) {
                return $this->apiError(trans('messages.invalid_refresh_token'));
            }

            $newAccessToken = JWTAuth::fromUser($user);
            $newRandom = Str::random(60);
            $newExpiry = now()->addDays(7)->timestamp;
            $newRefresh = $newRandom . '.' . $newExpiry;
            $user->refresh_token = hash('sha256', $newRefresh);
            $user->save();

            return $this->apiSuccess([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefresh,
            ], trans('messages.token_refreshed'));
        }
        catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.token_invalid'), 401);
        }

    }
}
