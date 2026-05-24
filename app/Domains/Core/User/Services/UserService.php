<?php

namespace App\Domains\Core\User\Services;

use App\Domains\Core\Role\Models\Role;
use App\Domains\Core\User\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use FateelTech\TaqnyatSmsLaravel\Facades\TaqnyatSms;
use App\Http\Controllers\APIController;
use App\Domains\Core\Setting\Models\Setting;
class UserService extends APIController
{
    public function createUpdateAdmin($request, $user=null, $type='admin')
    {
        $input = $request->only('name', 'email', 'phone', 'password', 'user_status', 'roles', 'profile_image');
        if(!$user){
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);
        } else {
            $user->update($input);
            if($type == 'master')
            {
                $masterData = $request->only('education', 'experience', 'tagline', 'biography', 'price_per_hour', 'available_time', 'available_day');
                $user->masterDetail()->updateOrCreate(
                    ['user_id' => $user->id], // condition
                    $masterData  
                );

                if($request->has('id_files')){
                    // dd($request->file('id_files'));
                    foreach ($request->file('id_files') as $file) {
                        $uploadId = null;
                        $actionType = 'save';
                        if($idFileRecord = $user->id_files){
                            $uploadId = $idFileRecord->id;
                            $actionType = 'update';
                        }
                        uploadImage($user, $file, 'user/id-files',"id_files", 'original', $actionType, $uploadId);
                    }
                }

                if($request->has('certificate')){
                    $uploadId = null;
                    $actionType = 'save';
                    if($certificateRecord = $user->certificate){
                        $uploadId = $certificateRecord->id;
                        $actionType = 'update';
                    }
                    uploadImage($user, $request->profile_image, 'user/certificate',"certificate", 'original', $actionType, $uploadId);
                }
                return response()->json([
                    'success' => true,
                    'message' => trans('messages.crud.update_record'),
                ], 200);


            }
           
        }

        // Upload/Update Profile Image
        if($request->has('profile_image')){
            $uploadId = null;
            $actionType = 'save';
            if($profileImageRecord = $user->profileImage){
                $uploadId = $profileImageRecord->id;
                $actionType = 'update';
            }
            uploadImage($user, $request->profile_image, 'user/profile-images',"user_profile", 'original', $actionType, $uploadId);
        }

        // Get Role Ids
        $roleIds = Role::whereIn('uuid', $request->roles)->pluck('id')->toArray();
        $user->roles()->sync($roleIds);

        return $user; 
    }

    /* public function registerUser($userData, $type="customer"){
        $userData['password'] = bcrypt($userData['password']);

        $cityId = City::where('uuid', $userData['city_id'])->first()->id;
        $userData['city_id'] = $cityId;
        
        $neighborhoodId = Neighborhood::where('uuid', $userData['neighborhood_id'])->first()->id;
        $userData['neighborhood_id'] = $neighborhoodId;

        // Save user to database
        $user = User::create($userData);

        $roleId = Role::where('name_en', ucfirst($userData['user_type']))->first()->id;
        if($user){
            $user->roles()->sync([$roleId]);

            if($userData['user_type'] == 'master'){
                
            }
        }
    } */

    public function sendOtp($country_code, $phone_number, $type="register"){
        $otp = rand(1000, 9999);
        // $otp = 1234;
        $appName = Setting::where('key', 'site_title')->value('value');
        $formattedNumber = ltrim($country_code, '+') . $phone_number;
        // $formattedNumber = $country_code . $phone_number; // +966 55 663 3222
        
        $message = trans('messages.otp_message', [
                    'otp' => $otp,
                    'app_name' => $appName,
                ]);
                // dd($message);
        // Send using Taqnyat
        $response = TaqnyatSms::sendMsg(
            $message,
            [$formattedNumber],
        );
        if ($response->statusCode === 201 && !empty($response->accepted)) {
            // Cache OTP only if SMS sent
            Cache::put('otp_' . $phone_number, $otp, now()->addMinutes(5));
            return $otp;
        }
        return false;
        // return $otp;
    }
}
