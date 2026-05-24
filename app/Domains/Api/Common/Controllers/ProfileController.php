<?php

namespace App\Domains\Api\Common\Controllers;

use App\Domains\Api\Common\Requests\ProfileRequest;
use App\Domains\Api\Common\Requests\PhoneNumberRequest;
use App\Domains\Api\Common\Requests\OtpRequest;
use App\Domains\Core\User\Models\User;
use App\Http\Controllers\APIController;
use App\Domains\Core\Specialty\Models\Specialty;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Domains\Admin\Specialty\Mail\SpecialtyAddedMail;
use App\Domains\Core\User\Services\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Domains\Api\Auth\Emails\UserSelectedOtherLocationMail;

class ProfileController extends APIController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function profile(){
        try {
            $authUser = JWTAuth::user();
            $user = User::with(['specialties', 'masterDetail', 'city', 'neighborhood'])->where('id', $authUser->id)->first();
            $role_id = $user->roles->first()?->id;
            $personalInfo = [
                'name'          => $user?->name,
                'email'         => $user?->email,
                'country_code'  => $user?->country_code,
                'phone'         => $user?->phone,
                'date_of_birth' => $user?->date_of_birth,
                'gender'        => $user?->gender,
                'profile_image' => $user?->profile_image_url,
                'city'          => ($user?->city_id == 0)
                                    ? trans('constant.other', [], $user->language ?? app()->getLocale())
                                    : ($user?->city?->{'name_' . ($user->language ?? 'en')} ?? ''),
                // 'city'          => $user?->city_id,
                'neighborhood'  => ($user?->neighborhood_id == 0)
                                    ? trans('constant.other', [], $user->language ?? app()->getLocale())
                                    : ($user?->neighborhood?->{'name_' . ($user->language ?? 'en')} ?? ''),
                // 'neighborhood'  => $user?->neighborhood_id,
            ];
            // Biography
            if($role_id == '3'){
                $biography = $user?->about_user;
                $userSpecialtyIds = $user->specialties->pluck('id')->toArray();
                $mainSpecialtyIds = Specialty::whereIn('id', $userSpecialtyIds)
                        ->whereNull('parent_specialty_id')
                        ->pluck('id')
                        ->toArray();

                // Step 3: Get all SUBJECTS (2nd tier) under these MAIN specialties
                $subjects = Specialty::whereIn('parent_specialty_id', $mainSpecialtyIds)
                                ->whereHas('parent', function ($q) {
                                    $q->whereNull('parent_specialty_id'); // Ensures it's really a child of a main
                                })
                                ->get();
                $subjectsMapped = $subjects->map(fn($s) => [
                    'id' => $s->id,
                    'name_en' => $s->name_en,
                    'name_ar' => $s->name_ar,
                ])->values();
                $selectedInterestIds = explode(',', $user->user_interest ?? '');
                $interestMapped = $subjects->whereIn('id', $selectedInterestIds)
                    ->map(fn($s) => [
                        'id' => $s->id,
                        'name_en' => $s->name_en,
                        'name_ar'  => $s->name_ar
                    ])->values();
                $gender_preference = $user?->gender_preference;
                $learning_mode = $user?->learning_mode;
            }
            else{
                $biography = $user?->masterDetail?->biography;
           
                // work prefernces
                $availableDays = array_map(function($day) use ($authUser){
                    return trans('constant.available_day', [], $authUser['language'])[$day];
                }, $user?->masterDetail?->available_day);

                $availableTimes = array_map(function($time) use ($authUser){
                    return trans('constant.available_time', [], $authUser['language'])[$time];
                }, $user?->masterDetail?->available_time);

                $workPrefernces = [
                    'price_per_hour' => $user?->masterDetail?->price_per_hour,
                    'available_time' => $availableTimes,
                    'available_day' => $availableDays
                ];

                // education
                $educations = array_map(function($education) use ($authUser){
                    return trans('constant.education', [], $authUser['language'] )[$education];
                }, $user?->masterDetail?->education);
                $gender_preference = $user?->gender_preference;
            }
            // Specialty
            // $specialties = $user?->specialties;
            $specialties = $user->specialties->map(function ($specialty) use ($user) {
                $locale = $user->language ?? app()->getLocale();
                // Translate level
                $translatedLevel = trans('constant.specialty_level.' . $specialty->pivot->level, [], $locale);

                // Replace pivot level with translated one
                $specialty->pivot->level = $translatedLevel;

                return $specialty;
            });
            $specialtyOptions = Specialty::with('childrenRecursive')->with('specialtyIcon')->where('specialty_status', 'active')->whereNull('parent_specialty_id')->get();
                
            if($role_id == '2'){
                $data = [
                    "profile_data" => $user,
                    'personal_inforamation' => $personalInfo,
                    'verified_icon'    => optional($user->activeSubscription?->plan)->verified_icon_url ?? "",
                    'biography' => $biography,
                    'gender_preference' => $gender_preference,
                    'specialties' => $specialties,
                    'certificates' => $user?->certificate_files_urls,
                    'work_prefernces' => $workPrefernces,
                    'experience' => trans('constant.experience.' . $user->masterDetail->experience, [], $user->language) ?? '',
                    'education' => $educations,
                    "field_options" => [
                        'specialties' => $specialtyOptions,
                        'specialty_level' => trans('constant.specialty_level', [], $user['language']),
                        'gender' => trans('constant.gender', [], $user['language']),
                        'gender_preference' => trans('constant.gender_preference', [], $user['language']),
                        'available_day' =>  trans('constant.available_day', [], $user['language']),
                        'available_time' => trans('constant.available_time', [], $user['language']),
                        'experience' => trans('constant.experience', [], $user['language']),
                        'education' => trans('constant.education', [], $user['language'])
                    ]
                ];
            }
            else{
                $data = [
                    "profile_data" => $user,
                    'personal_inforamation' => $personalInfo,
                    'biography' => $biography,
                    'interest' => $interestMapped,
                    'gender_preference' => $gender_preference,
                    'learning_mode' => $learning_mode,
                    'specialties' => $specialties,
                    "field_options" => [
                        'subjects' => $subjectsMapped,
                        'gender_preference' => trans('constant.gender_preference', [], $user['language']),
                        'learning_mode' => trans('constant.learning_mode', [], $user['language']),
                        'gender' => trans('constant.gender', [], $user['language']),
                        'specialty_level' => trans('constant.specialty_level', [], $user['language']),
                        'specialties' => $specialtyOptions,
                    ]
                ];
            }
            return $this->apiSuccess($data);
        } catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function updateProfile(ProfileRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth('api')->user();
            $input = $request->all();

            /**
             * ==============================
             * Common Fields (Customer/Master)
             * ==============================
             */
            if ($request->hasAny(['name','email','gender','city_id','neighborhood_id','date_of_birth'])) {
                $user->update($request->only(
                    'name','email','gender','city_id','neighborhood_id','date_of_birth'
                ));
            }
            $role = $user->roles()->first();
             if ($user->city_id == 0 || $user->neighborhood_id == 0) {
                $superAdmin = User::whereHas('roles', function ($query) {$query->where('name_en', 'Super Admin'); })->first();
                $subject = trans('emails.user_selected_other_location_mail.subject', [], $superAdmin->language);
                $roleName = $role ? ucwords($role->{'name_' . ($superAdmin->language ?? 'en')}): '';
                // dd($roleName);
                $user->load(['city', 'neighborhood']);
                Mail::to(getSetting('support_email'))->send(new UserSelectedOtherLocationMail($user, $superAdmin->language, $subject, $roleName));  // super-admin for other city or neighborhood
            }

            if ($request->has('profile_image')) {
                $uploadId = optional($user->profileImage)->id;
                $actionType = $uploadId ? 'update' : 'save';
                uploadImage($user, $request->profile_image, 'user/profile-images', "user_profile", 'original', $actionType, $uploadId);
            }

            /**
             * ==============================
             * Customer-specific Fields
             * ==============================
             */
            if ($user->hasRole('Learner')) {
                if ($request->has('about_user')) {
                    $user->update(['about_user' => $request->about_user]);
                }

                if ($request->has('user_interest')) {
                    $user->update(['user_interest' => implode(',', $request->user_interest)]);
                }

                if ($request->has('learning_mode')) {
                    $user->update(['learning_mode' => $request->learning_mode]);
                }

                if ($request->has('gender_preference')) {
                    $user->update(['gender_preference' => $request->gender_preference]);
                }
            }

            /**
             * ==============================
             * Master-specific Fields
             * ==============================
             */
            if ($user->hasRole('Master')) {
                if ($request->has('biography')) {
                    $user->masterDetail->update(['biography' => $request->biography]);
                }

                if ($request->has('specialties')) {
                    $specialtyData = $request->specialties;
                    $syncData = [];
                    foreach ($specialtyData as $specialty) {
                        if (isset($specialty['specialty_id'])) {
                            $syncData[$specialty['specialty_id']] = [
                                'level' => $specialty['level_id'] ?? 'beginner',
                            ];
                        }
                    }
                    $existingSpecialtyIds = $user->specialties()->pluck('specialties.id')->toArray();
                    $user->specialties()->sync($syncData);
                    $newSpecialtyIds = array_diff(array_keys($syncData), $existingSpecialtyIds);
                    foreach ($newSpecialtyIds as $specialtyId) {
                        $specialty = Specialty::find($specialtyId);
                        
                        if ($specialty && $specialty->specialty_request_id) {
                            $specialtyRequest = $specialty->specialtyRequest;
                            $alreadyHasMasters = DB::table('specialty_user')
                            ->where('specialty_id', $specialtyId)
                            ->where('user_id', '!=', $user->id)
                            ->exists();
                            if(!$alreadyHasMasters && $specialtyRequest && $specialtyRequest->user_info){
                                // send mail to guest user
                                $userInfo = json_decode($specialtyRequest ->user_info, true);
                                $email = $userInfo['user_email'] ?? null;
                                $user_data = User::where('email', $email)->with('roles')->first();
                                $superAdmin = User::whereHas('roles', function ($query) {
                                                $query->where('name_en', 'Super Admin');
                                            })->first();
                                $subject = trans('emails.speciality_added_by_master_mail_student.subject',[],$userInfo['user_language']);
                                if(!$user_data){
                                    Mail::to($userInfo['user_email'])->send(new SpecialtyAddedMail($userInfo['user_name'], $userInfo['user_language'], $superAdmin['email'], $subject, $specialty->name_en));
                                }
                                else{
                                    // send notification to master & customer
                                    if ($user_data && $user_data->id != $user->id) {
                                        $locale = $user_data->language ?? 'en';
                                        $column = 'name_'.$locale;
                                        $localizedName = $specialty->$column;
                                        sendUserNotification(
                                            $user_data->id,
                                            'specialty_added_by_master_notification_title',
                                            'specialty_added_by_master_notification_body',
                                            'specialty',
                                            NULL, 
                                            false,
                                            ['name' => $localizedName] 
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

                if ($request->has('certificate_files')) {
                    foreach ($request->file('certificate_files') as $file) {
                        uploadImage($user, $file,'user/certificates/certificate_files', 'certificate_file');
                    }
                }

                if ($request->has('deleted_user_certificate_files') &&  !empty($request->deleted_user_certificate_files)) {
                    $documentIds = explode(',', $request->deleted_user_certificate_files);
                    foreach ($documentIds as $documentId) {
                        deleteFile($documentId);
                    }
                }

                if ($request->hasAny(['price_per_hour','available_day','available_time'])) {
                    $user->masterDetail->update($request->only('price_per_hour','available_day','available_time'));
                }

                if ($request->has('experience')) {
                    $user->masterDetail->update(['experience' => $request->experience]);
                }

                if ($request->has('education')) {
                    $user->masterDetail->update(['education' => $request->education]);
                }

                if ($request->has('gender_preference')) {
                    $user->update(['gender_preference' => $request->gender_preference]);
                }
            }

            DB::commit();
            return $this->apiSuccess([], trans('messages.profile_updated_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function updateMobileNumber(PhoneNumberRequest $request){
        try {
            $authUser = JWTAuth::user();
            if ($request['country_code'] !== '+966') {
                return $this->apiError(trans('messages.register_messages.country_code_format'));
            }
            if ($request['phone'] == $authUser['phone']) {
                return $this->apiError(trans('messages.register_messages.phone_not_same'));
            }

            $otp = $this->userService->sendOtp($request['country_code'], $request['phone']);
            Log::info('OTP', ['OTP' => $otp]);
            if ($otp) {
                Cache::put('pending_update_' . $authUser->id, [
                    'country_code' => $request['country_code'],
                    'phone'        => $request['phone'],
                ], now()->addMinutes(5));
                return $this->apiSuccess([
                    'message' => trans('messages.register_messages.send_otp'),
                    'data' => ['otp' => $otp]
                ]);
            }
            return $this->apiError(trans('messages.register_messages.otp_failed'));
        } catch (\Throwable $th) {
            // dd($th);
            return [
                'status' => false,
                'message' => trans('messages.error_message'),
            ];
        }
    }

    public function checkOTPUpdateMobileNumber(OtpRequest $request){
        try{
            $authUser = JWTAuth::user();
            $message = '';
            $cachedOtp = Cache::get('otp_' . $request['phone']);
            if (!$cachedOtp) {
                return $this->apiError(trans('messages.register_messages.otp_expired'));
            } else if($cachedOtp != $request['otp']){
                return $this->apiError(trans('messages.register_messages.otp_invalid'));
            } else {
                $pendingUpdate = Cache::get('pending_update_' . $authUser->id);
                if ($pendingUpdate && $pendingUpdate['phone'] == $request['phone']) {
                    $authUser->update([
                        'country_code' => $request['country_code'],
                        'phone'        => $request['phone'],
                    ]);
                    Cache::forget('otp_' . $request['phone']);
                    Cache::forget('pending_update_' . $authUser->id);
                    return $this->apiSuccess(trans('messages.register_messages.mobile_updated'));
                }else {
                    $message = trans('messages.error_message');
                }
            }    
            return $this->apiError(trans($message));
        }
        catch(\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }
}
