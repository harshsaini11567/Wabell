<?php

namespace App\Domains\Admin\Dashboard\Controllers;

use App\Domains\Core\Specialty\Models\Specialty;
use App\Domains\Core\Subscription\Models\Transactions;
use App\Domains\Core\User\Models\User;
use App\Http\Controllers\Controller;
use App\Rules\MatchOldPassword;
use App\Rules\NoMultipleSpacesRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Domains\Core\City\Models\City;
class DashboardController extends Controller
{
    public function index()
    {
        $masterCount = User::whereHas('roles', function($q) {
            $q->where('id', config('constant.roles.master'));
        })->count();

        $customerCount = User::whereHas('roles', function($q) {
           $q->where('id', config('constant.roles.customer'));
        })->count();

        $specialityCount = Specialty::where('parent_specialty_id',NULL)->count();
        $transactionRevanue = Transactions::where('payment_status','paid')->sum('amount');
        $specialities = Specialty::select('id','name_en')->where('parent_specialty_id',NULL)->get();
        // $cities = User::select('city_id')->whereNotNull('city_id')->groupBy('city_id')->get();
        $cities = City::select('id', 'name_en')
        ->whereIn('id', User::whereNotNull('city_id')->pluck('city_id')->unique())
        ->orderBy('name_en')
        ->get();
        $masterCitySpecility = DB::table('users')
                ->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->join('specialty_user', 'users.id', '=', 'specialty_user.user_id')
                ->select('users.city_id', 'specialty_user.specialty_id', DB::raw('COUNT(*) as total'))
                ->where('role_user.role_id', config('constant.roles.master'))
                ->whereNotNull('users.city_id')
                ->groupBy('users.city_id', 'specialty_user.specialty_id')
                ->get()
                ->groupBy('city_id');

        $customerCitySpecility = DB::table('users')
                ->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->join('specialty_user', 'users.id', '=', 'specialty_user.user_id')
                ->select('users.city_id', 'specialty_user.specialty_id', DB::raw('COUNT(*) as total'))
                ->where('role_user.role_id', config('constant.roles.customer'))
                ->whereNotNull('users.city_id')
                ->groupBy('users.city_id', 'specialty_user.specialty_id')
                ->get()
                ->groupBy('city_id');
        return view('Dashboard::index',compact('masterCount', 'customerCount','specialityCount','transactionRevanue','specialities','cities','masterCitySpecility','customerCitySpecility'));
    }

    public function getHeatmapData(){
        $masterCitySpecility = DB::table('users')
        ->join('role_user', 'users.id', '=', 'role_user.user_id')
        ->join('specialty_user', 'users.id', '=', 'specialty_user.user_id')
        ->select('users.city_id', 'specialty_user.specialty_id', DB::raw('COUNT(*) as total'))
        ->where('role_user.role_id', config('constant.roles.master'))
        ->whereNotNull('users.city_id')
        ->groupBy('users.city_id', 'specialty_user.specialty_id')
        ->get()
        ->groupBy('city_id');

        // Build Learner counts
        $customerCitySpecility = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('specialty_user', 'users.id', '=', 'specialty_user.user_id')
            ->select('users.city_id', 'specialty_user.specialty_id', DB::raw('COUNT(*) as total'))
            ->where('role_user.role_id', config('constant.roles.customer'))
            ->whereNotNull('users.city_id')
            ->groupBy('users.city_id', 'specialty_user.specialty_id')
            ->get()
            ->groupBy('city_id');

        // Get specialties and cities
        $specialties = Specialty::select('id','name_en')
            ->whereNull('parent_specialty_id')
            ->get();

        // $cities = User::select('city_id')
        //     ->whereNotNull('city_id')
        //     ->groupBy('city_id')
        //     ->get();
        $cities = City::select('id', 'name_en')
                ->whereIn('id', User::whereNotNull('city_id')->pluck('city_id')->unique())
                ->orderBy('name_en')
                ->get();

        $data = [];
        foreach ($cities as $city) {
            foreach ($specialties as $specialty) {
                $studentCount = ($customerCitySpecility[$city->id] ?? collect())
                    ->firstWhere('specialty_id', $specialty->id)->total ?? 0;

                $masterCount = ($masterCitySpecility[$city->id] ?? collect())
                    ->firstWhere('specialty_id', $specialty->id)->total ?? 0;

                $ratio = $masterCount == 0 ? null : number_format($studentCount / $masterCount, 1);

                $data[] = [
                    "city" => $city->name_en,
                    "specialty" => $specialty->name_en,
                    "master"    => $masterCount,
                    "student" => $studentCount,
                    "ratio" => $ratio,
                ];
            }
        }
        return response()->json($data);
    }

    public function showprofile(){
        $user = auth()->user();
        return view('Dashboard::profile', compact('user'));
    }

    public function updateprofile(Request $request){

        $user = auth()->user();
        $updateRecords = [
            'name'  => ['required', 'regex:/^[a-zA-Z\s]+$/', 'string', 'max:255', new NoMultipleSpacesRule],
            'profile_image'  =>['nullable', 'image', 'max:'.config('constant.profile_max_size'), 'mimes:jpeg,png,jpg'],
        ];

        $request->validate($updateRecords,[
            'phone.required'=>'The phone number field is required',
            'phone.regex' =>'The phone number length must be 7 to 15 digits.',
            'phone.unique' =>'The phone number already exists.',
            'profile_image.image' =>'Please upload image.',
            'profile_image.mimes' =>'Please upload image with extentions: jpeg,png,jpg.',
            'profile_image.max' =>'The image size must equal or less than '.config('constant.profile_max_size_in_mb'),
        ]);
        
        if($request->ajax()){
            DB::beginTransaction();
            try {
                $user->update($request->all());

                if($request->has('profile_image')){
                    $uploadId = null;
                    $actionType = 'save';
                    if($profileImageRecord = $user->profileImage){
                        $uploadId = $profileImageRecord->id;
                        $actionType = 'update';
                    }
                    uploadImage($user, $request->profile_image, 'user/profile-images',"user_profile", 'original', $actionType, $uploadId);
                }
                DB::commit();

                $user = User::where('id', $user->id)->first();
                
                $data = [
                    'success' => true,
                    'profile_image' => $user->profile_image_url,
                    'auth_name' => $user->name,
                    'message' => __('messages.crud.update_record'),
                ];
                return response()->json($data, 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => __('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => __('messages.error_message')], 400 );
    }

    public function updateChangePassword(Request $request){
        $user = auth()->user();
        $request->validate([
            'current_password'  => ['required', 'string','min:8', new MatchOldPassword],
            'password'   => ['required', 'string', 'min:8', 'different:current_password', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/'],
            'password_confirmation' => ['required','min:8','same:password'],

        ], getCommonValidationRuleMsgs());
        if($request->ajax()){
            DB::beginTransaction();
            try {
                $user->update(['password'=> Hash::make($request->password)]);
                DB::commit();
                $data = [
                    'success' => true,
                    'message' => __('passwords.reset'),
                ];
                return response()->json($data, 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => __('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => __('messages.error_message')], 400 );
    }

    public function removeProfileImage(Request $request){
        if($request->ajax()){
            DB::beginTransaction();
            try {
                $user = auth()->user();
                
                $profileImage = $user->profileImage;
                if($profileImage && isset($profileImage->id)){
                    deleteFile($profileImage->id);

                    DB::commit();
                    $data = [
                        'success' => true,
                        'profile_image' => asset(config('constant.default.user_icon')),
                        'auth_name' => $user->name,
                        'message' => __('messages.crud.profile.remove_image'),
                    ];
                    return response()->json($data);
                } else {
                    return response()->json(['success' => false, 'error' => __('messages.crud.profile.remove_image_not_found')], 400 );
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => __('messages.error_message')], 400 );
            }
        } 
    
    }
}
