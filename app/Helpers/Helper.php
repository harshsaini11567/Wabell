<?php

use App\Domains\Core\Setting\Models\Setting;
use App\Domains\Core\Upload\Models\Uploads;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str as Str;
use App\Notifications\SendNotification;
use App\Domains\Core\User\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

if (!function_exists('getCommonValidationRuleMsgs')) {
	function getCommonValidationRuleMsgs()
	{
		return [
            'currentpassword.required'=>'The current password is required.',
			'password.required' => 'The new password is required.',
			'password.min' => 'The new password must be at least 8 characters',
			'password.different' => 'The new password and current password must be different.',
			'password.confirmed' => 'The password confirmation does not match.',
			'password_confirmation.required' => 'The new password confirmation is required.',
			'password_confirmation.min' => 'The new password confirmation must be at least 8 characters',
			'email.required' => 'Please enter email address.',
			'email.email' => 'Email is not valid. Enter email address for example test@gmail.com',
            'email.exists' => "Please Enter Valid Registered Email!",
            'password_confirmation.same' => 'The confirm password and new password must match.',

			'password.regex' => 'The :attribute must be at least 8 characters and contain at least one uppercase character, one number, and one special character.',
			'password.regex' => 'The :attribute must be at least 8 characters and contain at least one uppercase character, one number, and one special character.',
		];
	}
}

if (!function_exists('generateRandomString')) {
	function generateRandomString($length = 20) {
		$randomString = Str::random($length);
		return $randomString;
	}
}

if (!function_exists('getWithDateTimezone')) {
	function getWithDateTimezone($date) {
        $newdate= Carbon::parse($date)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
		return $newdate;
	}
}

if (!function_exists('uploadImage')) {
	/**
	 * Upload Image.
	 *
	 * @param array $input
	 *
	 * @return array $input
	 */
	function uploadImage($directory, $file, $folder, $type="profile", $fileType="jpg",$actionType="save",$uploadId=null,$orientation=null)
	{
		$oldFile = null;
        if($actionType == "save"){
			$upload               		= new Uploads;
		}else{
			$upload               		= Uploads::find($uploadId);
			$oldFile = $upload->file_path;
		}
        $upload->file_path      	= $file->store($folder, 'public');
		$upload->extension      	= $file->getClientOriginalExtension();
		$upload->original_file_name = $file->getClientOriginalName();
		$upload->type 				= $type;
		$upload->file_type 			= $fileType;
		$upload->orientation 		= $orientation;
		$response             		= $directory->uploads()->save($upload);
        // delete old file
        if ($oldFile) {
            Storage::disk('public')->delete($oldFile);
        }

		return $upload;
	}
}

if (!function_exists('deleteFile')) {
	/**
	 * Destroy Old Image.	 *
	 * @param int $id
	 */
	function deleteFile($upload_id)
	{
		$upload = Uploads::find($upload_id);
		Storage::disk('public')->delete($upload->file_path);
		$upload->delete();
		return true;
	}
}

if (!function_exists('getSetting')) {
	function getSetting($key)
	{
		$result = null;
		$setting = Setting::where('key', $key)->where('status', 1)->first();

		if (!$setting) {
			return null;
		}

		if ($setting->type == 'image') {
			$result = $setting->image_url;
		} elseif ($setting->type == 'file') {
			$result = $setting->doc_url;
		} elseif ($setting->type == 'json') {
			$result = $setting->value ? json_decode($setting->value, true) : null;
		} else {
			$result = $setting->value;
		}
		
		return $result;
	}
}

if (!function_exists('str_limit_custom')) {
    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     * @return string
     */
    function str_limit_custom($value, $limit = 100, $end = '...')
    {
        return \Illuminate\Support\Str::limit($value, $limit, $end);
    }
}

if (!function_exists('getSvgIcon')) {
    function getSvgIcon($icon){
        return view('components.svg-icons', ['icon' => $icon])->render();
    }
}

if (!function_exists('dateFormat')) {
	function dateFormat($date, $format=''){
		$startDate = Carbon::parse($date);
		$formattedDate = $startDate->format($format);
		return $formattedDate;
	}
}

if (!function_exists('generateSlug')) {

	function generateSlug($name,$tableName, $ignoreId = null)
	{
		// Convert the name to a slug
		$slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));

		// Ensure no multiple hyphens
		$slug = preg_replace('/-+/', '-', $slug);

		// Trim hyphens from both ends
		$slug = trim($slug, '-');

		// Ensure the slug is unique
		$originalSlug = $slug;
		$count = 1;

		$query = DB::table($tableName)->where('slug', $slug)->whereNull('deleted_at');
		
		// Ignore the current record if updating
		if ($ignoreId) {
			$query->where('id', '!=', $ignoreId);
		}

		while ($query->exists()) {
			$slug = "{$originalSlug}-{$count}";
			$count++;
			
			// Update the query to check for the new slug
			$query = DB::table($tableName)->where('slug', $slug)->whereNull('deleted_at');
			if ($ignoreId) {
				$query->where('id', '!=', $ignoreId);
			}
		}
		
		return $slug;
	}
}

if (!function_exists('PaginationSettings')) {
    function PaginationSettings(string $module): array
    {
		$defaultOptions = [10, 25, 50, 100];
        $value = (int)(getSetting($module) ?? 10);

		$allOptions = collect($defaultOptions)
            ->push($value)
            ->unique()
            ->sort()
            ->values();

        return [
            'pageLength' => $value,
            'lengthMenu' => [
                $allOptions->all(),                               // numeric
                $allOptions->map(fn($v) => (string) $v)->all(),   // string
            ],
        ];
    }
}

if (!function_exists('sendUserNotification')) {
    function sendUserNotification($userId, $title, $message, $type = null, $url = null, $sendMail = false, $replacements = [])
    {
        $user = User::find($userId);
		
        if ($user) {
			$locale = $user->language ?? 'en';
			if (is_array($title)) {
                $localizedTitle = $title; 
				$titleForUser = $title[$locale] ?? $title['en'];
            } else {
				$localizedTitle = [
					'en' => trans('messages.notifications.'.$title, [], 'en'),
					'ar' => trans('messages.notifications.'.$title, [], 'ar'),
				];
				$titleForUser = $localizedTitle[$locale];
			}

			if (is_array($message)) {
                $localizedMessage = $message;
				$messageForUser = $message[$locale] ?? $message['en'];
            } else {
				$localizedMessage = [
					'en' => trans('messages.notifications.'.$message, $replacements, 'en'),
					'ar' => trans('messages.notifications.'.$message, $replacements, 'ar'),
				];
				$messageForUser = $localizedMessage[$locale];
			}

            $user->notify(new SendNotification(
                $localizedTitle,
                $localizedMessage,
                $type,
                $url,
                $sendMail
            ));

			// FCM Push Notification (via Firebase)
            try {
                $firebase = app(FirebaseService::class);
                if (!empty($user->device_token)) {
                    $firebase->sendToDevice(
                        $user->device_token,
                        // $localizedTitle['en'],
                        // $localizedMessage['en'],
						$titleForUser,
						$messageForUser,
                        [
                            'type' => $type ?? 'general',
                            'url'  => $url ?? '',
                        ]
                    );
                	Log::info('FCM sent successfully to user ID: ' . $user->id .', '. $locale );
				} else {
					Log::warning('No device token found for user ID: ' . $user->id);
				}
            } catch (\Exception $e) {
                // Log and continue, don't break notification if FCM fails
                Log::error('FCM Notification Failed: '.$user->id.' '. $e->getMessage());
            }
        }
    }
}
