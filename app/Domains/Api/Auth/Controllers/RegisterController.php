<?php

namespace App\Domains\Api\Auth\Controllers;

use App\Domains\Api\Auth\Requests\RegisterRequest;
use App\Domains\Core\City\Models\City;
use App\Domains\Core\City\Models\Neighborhood;
use App\Domains\Core\Role\Models\Role;
use App\Domains\Core\Specialty\Models\Specialty;
use App\Domains\Core\Specialty\Models\SpecialtyRequest;
use App\Domains\Core\User\Models\MasterDetail;
use App\Domains\Core\User\Models\User;
use App\Domains\Core\User\Services\UserService;
use App\Http\Controllers\APIController;
use App\Rules\NoMultipleSpacesRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use App\Domains\Admin\Specialty\Mail\SpecialtyRequestMail;
use App\Domains\Core\Setting\Models\Setting;
use App\Domains\Core\SplashScreen\Models\SplashScreen;
use App\Domains\Api\Auth\Emails\NewUserRegisteredMail;
use App\Domains\Api\Auth\Emails\WelcomeUserMail;
use App\Domains\Api\Auth\Emails\WelcomeUserMailMaster;
use App\Domains\Api\Auth\Emails\UserSelectedOtherLocationMail;
use App\Domains\Admin\User\Resource\UserResource;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
class RegisterController extends APIController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(Request $request)
    {    
        $step = $request->query('step');

        $input = $request->all();
        $input['language'] = $request->header('language', 'en');
        switch ($step) {
            case '1': // User Details
                app(RegisterRequest::class)->validated();                    
                $response = $this->registerStep1($input);
                
                break;

            case '2': // OTP verify
                $request->validate([
                    'otp' => 'required|digits:4',
                ],[],['otp' => trans('cruds.api.otp'),]);

                $response = $this->registerStep2($input);
                break;

            case '3': // Specialties
                $request->validate([
                    'specialties' => ['nullable', 'array', 'min:1'],
                    'specialties.*.specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
                    // 'specialties.*.level_id' => ['required', 'in:'.implode(',', array_keys(config('constant.specialty_level')))]                    
                ],[],['specialties' => trans('cruds.api.specialties')]);

               $response = $this->registerStep3($input);

                break;
            
            case '4': // education and experience
                $request->validate([
                    'experience' => ['required','string'],
                    'education' => ['required', 'array', 'min:1'],
                    'education.*' => ['required', 'in:'.implode(',', array_keys(config('constant.education')))]
                ],[],
                [
                    'experience' => trans('cruds.api.experience'),
                    'education' => trans('cruds.api.education'),
                ]);

                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => [],
                ];
                break;
            
            case '5':  // Upload Certificates
                $request->validate([
                    'id_files' => ['required', 'array', 'min:1'],
                    'id_files.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

                    'certificate_files' => ['nullable', 'array', 'min:1'],
                    'certificate_files.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                ],[],
                [
                    'id_files' => trans('cruds.api.id_files'),
                    'certificate_files' => trans('cruds.api.certificate_files'),
                ]
            );

                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => [],
                ];
                break;
            
            case '6':  // Biography
                $request->validate([
                    'tagline' => ['required', 'string'],
                    'biography' => ['required', 'string'],
                ],[],[
                    'tagline' => trans('cruds.api.tagline'),
                    'biography' =>trans('cruds.api.biography')
                ]);

                $data = [
                    'available_day' => trans('constant.available_day', [], $input['language']),
                    'available_time' =>  trans('constant.available_time', [], $input['language']),
                ];

                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data,
                ];
                break;
            
            case '7':  // Work Details
                $request->validate([
                    'price_per_hour' => ['required', 'numeric'],
                    'available_day' => ['required', 'array', 'min:1'],
                    'available_day.*' => ['required', 'in:'.implode(',', array_keys(config('constant.available_day')))],

                    'available_time' => ['required', 'array', 'min:1'],
                    'available_time.*' => ['required', 'in:'.implode(',', array_keys(config('constant.available_time')))]
                ],[],[
                    'price_per_hour' => trans('cruds.api.price_per_hour'),
                    'available_day' => trans('cruds.api.available_day'),
                    'available_time' => trans('cruds.api.available_time'),
                ]);

                $response = $this->lastStep($request);
                break;

            default:
                # code...
                break;
        }
        if(isset($response['status']) && !empty($response['status'])){
            return $this->apiSuccess($response['data'] ?? [], $response['message']);
        } else {
            return $this->apiError($response['message']);
        }
    }

    public function registerStep1($input){
        try {
            if ($input['country_code'] !== '+966') {
                return [
                    'status' => false,
                    'message' => 'OTP can only be sent to Saudi Arabian numbers (+966).',
                ];
            }
            $otp = $this->userService->sendOtp($input['country_code'], $input['phone']);
            Log::info('OTP', ['OTP' => $otp]);
            if ($otp) {
                return [
                    'status' => true,
                    'message' => trans('messages.register_messages.send_otp'),
                    'data' => ['otp' => $otp],
                ];
            }
            return [
                'status' => false,
                'message' => trans('messages.register_messages.otp_failed'), // create this lang line
            ];
        } catch (\Throwable $th) {
            // dd($th);
            return [
                'status' => false,
                'message' => trans('messages.error_message'),
            ];
        }
    }

    
    public function registerStep2($input){
        try {
            $data = [];
            $message = "";
            $status = true;

            // Check OTP 
            $cachedOtp = Cache::get('otp_' . $input['phone']);
            // dd($cachedOtp);
            if (!$cachedOtp) {
                $status = false;
                $message = trans('messages.register_messages.otp_expired');
            } else if($cachedOtp != $input['otp']){
                $status = false;
                $message = trans('messages.register_messages.otp_invalid');
            }            
            
            if($status == true){
                Cache::forget('otp_' . $input['phone']);
                
                $message = trans('messages.register_messages.success');

                $specialties = Specialty::with('childrenRecursive')->with('specialtyIcon')->where('specialty_status', 'active')->whereNull('parent_specialty_id')->get();
                $data['levels'] = trans('constant.specialty_level',[] , $input['language']);
                $data['specialties'] = $specialties;
            }

            return [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }
    
    public function registerStep3($input)
    {
        // dd($input);
        try {
            $data = [];
            $message = "";
            $status = true;
            if($input['user_type'] == 'customer'){
                DB::beginTransaction();
 
                $input['password'] = $input['register_type'] == 'normal' ? bcrypt($input['password']) : NULL;
                
                if($input['register_type'] != 'normal'){
                    $input['login_type'] = $input['register_type'];
                    $input['social_user_id'] = $input['social_user_id'];
                }
                $input['gender_preference'] = $input['master_gender_preference'] ?? null;

                $input['gender'] = $input['gender'] ?? null;
                // Save user to database
                $user = User::create($input);
 
                if($user){
                    $roleId = Role::where('name_en', ucfirst('learner'))->first()->id;
                    $user->roles()->sync([$roleId]);
                    $superAdmin = User::whereHas('roles', function ($query) {$query->where('name_en', 'Super Admin'); })->first();
                    $welcomeSubject = trans('emails.user_register_welcome_mail_student.subject', [], $user->language);
                    Mail::to($user->email)->send(new WelcomeUserMail($user, $welcomeSubject));   // new user: welcome mail
                    $newUserRegisterSubject = trans('emails.user_register_mail_super_admin.subject', [], $superAdmin->language);
                    Mail::to(getSetting('support_email'))->send(new NewUserRegisteredMail($superAdmin->name, $superAdmin->language, $user->name, $user->email, 'Learner', $newUserRegisterSubject, $user->phone));   // super admin: new user register
                }
                $syncData = [];
                // $specialtyData = $input['specialties'];
                // foreach ($specialtyData as $specialty) {
                //     $syncData[$specialty['specialty_id']] = ['level' => $specialty['level_id']];
                // }
                if (!empty($input['specialties'])) {
                    $specialtyIds = array_column($input['specialties'], 'specialty_id');
                    if (!empty($specialtyIds)) {
                        $user->specialties()->sync($specialtyIds);
                    }
                }
                // $user->specialties()->sync($syncData);
                
                DB::commit();
                app()->setLocale($user->language ?? 'en');
                $message = trans('messages.register_messages.success');

                $user->increment('token_version');
                $user->refresh();
                $customClaims = ['token_version' => $user->token_version];
                $token = JWTAuth::claims($customClaims)->fromUser($user);
                $userResource = new UserResource($user);
                $data = [
                    'access_token' => $token,
                    'user' => $userResource,
                    'user_logo' => getSetting('student_logo'),
                ];
            }
            else{
                $data = [
                    'education' =>trans('constant.education', [], $input['language']),
                    'experience' => trans('constant.experience', [], $input['language'])
                ];
            }
            return [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ];
        }
        catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => trans('messages.error_message'),
            ];
            // return $this->apiError(trans('messages.error_message'));
        }
    }
 
 
    public function lastStep($request){
        DB::beginTransaction();
        try {
            $message = "";
            $status = true;
 
            // User Data from Cache
            if($status == true){
                $inputs = $request->all();
                $inputs['language'] = $request->header('language', 'en');
 
                // Save user to database
                $userData = [
                    'name' => $inputs['name'],
                    'email' => $inputs['email'],
                    'country_code' => $inputs['country_code'],
                    'phone' => $inputs['phone'],
                    'city_id' => $inputs['city_id'],
                    'neighborhood_id' => $inputs['neighborhood_id'],
                    'phone_varified' => true,
                    'password' => $inputs['register_type'] == 'normal' ? bcrypt($inputs['password']) : NULL,
                    'latitude' => $inputs['latitude'],
                    'longitude' => $inputs['longitude'],
                    'user_status' => 'active',
                    'language' => $inputs['language'],
                    'is_approved' => 1,
                    'gender' => $inputs['gender'] ?? null,
                    'gender_preference' => $inputs['master_gender_preference'] ?? null
                ];
 
                if($inputs['register_type'] != 'normal'){
                    $userData['login_type'] = $inputs['register_type'];
                    $userData['social_user_id'] = $inputs['social_user_id'];
                }
                $user = User::create($userData);
 
                if($user){
                    $roleId = Role::where('name_en', ucfirst($inputs['user_type']))->first()->id;
                    $user->roles()->sync([$roleId]);
 
                    // upload specialty for user
                    $syncData = [];
                    $specialtyData = json_decode($inputs['specialties'] ?? '[]', true);
                    if (!empty($specialtyData)) {
                        foreach ($specialtyData as $specialty) {
                            if (!empty($specialty['specialty_id']) && !empty($specialty['level_id'])) {
                                $syncData[$specialty['specialty_id']] = ['level' => $specialty['level_id']];
                            }
                        }
                    }
                    if (!empty($syncData)) {
                        $user->specialties()->sync($syncData);
                    }
                    // upload master Details
                    MasterDetail::create([
                        'user_id' => $user->id,
                        'experience' => $inputs['experience'],
                        'education' => $inputs['education'],
                        
                        'tagline' => $inputs['tagline'],
                        'biography' => $inputs['biography'],
                        
                        'price_per_hour' => $inputs['price_per_hour'],
                        'available_day' => $inputs['available_day'],
                        'available_time' => $inputs['available_time']
                    ]);
 
                    // Upload Certificates
                    // Id files
                    if($request->has('id_files')){
                        foreach($request->file('id_files') as $file)
                        {
                            uploadImage($user, $file,'user/certificates/id_files', 'id_file'); 
                        }
                    }
 
                    // Id files
                    if($request->has('certificate_files')){
                        foreach($request->file('certificate_files') as $file)
                        {
                            uploadImage($user, $file,'user/certificates/certificate_files', 'certificate_file'); 
                        }
                    }

                    $superAdmin = User::whereHas('roles', function ($query) {$query->where('name_en', 'Super Admin'); })->first();
                    $welcomeSubject = trans('emails.user_register_welcome_mail_master.subject',[], $user->language);
                    Mail::to($user->email)->send(new WelcomeUserMailMaster ($user, $welcomeSubject));   // new user: welcome mail
                    $newUserRegisterSubject = trans('emails.user_register_mail_super_admin.subject', [], $superAdmin->language);
                    Mail::to(getSetting('support_email'))->send(new NewUserRegisteredMail($superAdmin->name, $superAdmin->language, $user->name, $user->email, ucwords($inputs['user_type']), $newUserRegisterSubject, $user->phone));   // super admin: new user register
                    if ($user->city_id == 0 || $user->neighborhood_id == 0) {
                        $subject = trans('emails.user_selected_other_location_mail.subject', [], $superAdmin->language);
                        $user->load(['city', 'neighborhood']);
                        Mail::to(getSetting('support_email'))->send(new UserSelectedOtherLocationMail($user, $superAdmin->language, $subject, ucwords($inputs['user_type'])));  // super-admin for other city or neighborhood
                    }
                }

                app()->setLocale($user->language ?? 'en');

                $user->increment('token_version');
                $user->refresh();
                $customClaims = ['token_version' => $user->token_version];
                $token = JWTAuth::claims($customClaims)->fromUser($user);
                $userResource = new UserResource($user);
                $data = [
                    'access_token' => $token,
                    'user' => $userResource,
                    'user_logo' => getSetting('master_logo'),
                ];
                $message = trans('messages.register_messages.success');
 
                DB::commit();
            }
 
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ];
 
            return $response;
        } catch (\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function getCities(){
        try {
            $cities = City::where('status', 'active')->select('id', 'name_en', 'name_ar','lat', 'lng')->orderBy('name_en', 'asc')->get();
            $otherCity = [
                'id' => 0,
                'name_en' => trans('constant.other'),
                'name_ar' => trans('constant.other',[],'ar'),
                'lat' => null,
                'lng' => null,
            ];
            $cities->push($otherCity);
            return $this->apiSuccess(['cities' => $cities]);
        } catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function getNeighborhoods($cityId){
        try {
            $neighborhoods = Neighborhood::where('status', 'active')->where('city_id', $cityId)->select('id', 'name_en', 'name_ar', 'lat', 'lng')->orderBy('name_en', 'asc')->get();
            $otherNeighborhood = [
                'id' => 0,
                'name_en' => trans('constant.other'),
                'name_ar' => trans('constant.other',[],'ar'),
                'lat' => null,
                'lng' => null,
            ];

            $neighborhoods->push($otherNeighborhood);
            return $this->apiSuccess(['neighborhoods' => $neighborhoods]);
        } catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    protected function buildNestedHierarchy($specialty)
    {
        $locale = App::getLocale() ?? 'en';

        // Prepare current specialty with its children
        $current = [
            'id' => $specialty->id,
            'uuid' => $specialty->uuid,
            'name' => $specialty->{'name_' . $locale},
            'specialty_status' => $specialty->specialty_status,
            'parent_specialty_id' => $specialty->parent_specialty_id,
            'specialty_icon_url' => optional($specialty->specialtyIcon)->file_url,
            'children_recursive' => $this->getDescendants($specialty),
        ];

        // Climb up to root by wrapping inside parents
        while ($specialty->parent_specialty_id) {
            $specialty = $specialty->parentSpecialty()->with('specialtyIcon')->first();
            if (!$specialty) break;

            $current = [
                'id' => $specialty->id,
                'uuid' => $specialty->uuid,
                'name' => $specialty->{'name_' . $locale},
                'specialty_status' => $specialty->specialty_status,
                'parent_specialty_id' => $specialty->parent_specialty_id,
                'specialty_icon_url' => optional($specialty->specialtyIcon)->file_url,
                'children_recursive' => [$current],
            ];
        }

        return $current;
    }

    protected function getDescendants($specialty)
    {
        $locale = App::getLocale() ?? 'en';
        $result = [];

        $children = $specialty->childSpecialties()->with(['childSpecialties', 'specialtyIcon'])->get();

        foreach ($children as $child) {
            $result[] = [
                'id' => $child->id,
                'uuid' => $child->uuid,
                'name' => $child->{'name_' . $locale},
                'specialty_status' => $child->specialty_status,
                'parent_specialty_id' => $child->parent_specialty_id,
                'specialty_icon_url' => optional($child->specialtyIcon)->file_url,
                'children_recursive' => $this->getDescendants($child),
            ];
        }

        return $result;
    }

    public function searchSpecialty(Request $request)
    {
        try {
            $query = $request->query('search');
            $locale = App::getLocale() ?? 'en';

            // Find specialties matching the search
            $matchedSpecialties = Specialty::where("name_$locale", 'LIKE', "%{$query}%")
                ->with(['parentSpecialty', 'specialtyIcon', 'childSpecialties'])
                ->get();

            $finalResult = [];
            $addedRootIds = [];

            foreach ($matchedSpecialties as $specialty) {
                // $finalResult[] = $this->buildNestedHierarchy($specialty);
                $nested = $this->buildNestedHierarchy($specialty);

                // Avoid adding duplicates based on the root id
                if (!in_array($nested['id'], $addedRootIds)) {
                    $finalResult[] = $nested;
                    $addedRootIds[] = $nested['id'];
                }
            }

            return $this->apiSuccess(['specialties' => $finalResult]);

        } catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function storeRequestSpecialty(Request $request){
        $lang = $request->language;
        if($lang == 'en'){
            $col = 'name_en';
        }
        else{
            $col = 'name_ar';
        }
        $request->validate([
            'language'  => ['required',"in:ar,en"],
            'user_email'     => ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i'],
            'user_name'     => ['required', 'regex:/^[a-zA-Z\s]+$/', 'string', 'max:255', new NoMultipleSpacesRule],
            'name'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:specialty_requests,'.$col.',NULL,uuid,deleted_at,NULL'],
        ],[],[
            'language' => trans('cruds.api.language'),
            'user_email' => trans('cruds.api.email'),
            'user_name' => trans('cruds.api.name'),
            'name' => trans('cruds.api.specialty_name'),
        ]);
        try {
            DB::beginTransaction();
            $userInfo = [
                'user_email' => $request->user_email,
                'user_name'  => $request->user_name,
                'user_language' => $request->language,
            ];
            $input = [$col => ucwords($request->name),
                'user_info' => json_encode($userInfo),
                'user_role' => $request->user_role,
            ];

            SpecialtyRequest::create($input);
            $superAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name_en', 'Super Admin');
                })->first();

            $subject = trans('emails.speciality_request_mail_super_admin.subject', [], $superAdmin['language']);
            $specialty_request_url = config('app.url') . '/specialty-requests';
            Mail::to(getSetting('support_email'))->send(new SpecialtyRequestMail($superAdmin['name'], $superAdmin['language'], $request->email, $subject, $specialty_request_url));        
            DB::commit();
            return $this->apiSuccess([], trans('messages.specialty_request_send_for_approval'));
        } catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function splashRecord(Request $request){
        try {
           $lang = $request->header('language', 'en');
            $splashScreens = SplashScreen::where('status','active')->orderBy('position','asc')->get()->map(function ($item) use ($lang) {
                return [
                    'id' => $item->id,
                    'title' => $lang === 'ar' ? $item->title_ar : $item->title_en,
                    'description' => $lang === 'ar' ? $item->description_ar : $item->description_en,
                    'image_url' => $item->splash_image_url, // assuming you have an accessor for full URL
                ];
            });
            $data = ['onBoardingScreens' => $splashScreens];
            return $this->apiSuccess($data);
        }
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function welcomeVideo(){
        try{
            $userType = request()->query('user_type');
            $welcomeVideo = Setting::with('image')->where('key', $userType.'_welcome_video')->first();
            if ($welcomeVideo && $welcomeVideo->image_url) {
                 return $this->apiSuccess(['video_url' => $welcomeVideo->image_url]);
            }
            $defaultVideo = asset(config('constant.default.'.$userType.'_welcome_video'));
            return $this->apiSuccess(['video_url' => $defaultVideo]);
        }
         catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }
}
