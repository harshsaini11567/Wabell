<?php

namespace App\Domains\Admin\Setting\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Core\Setting\Models\Setting;
use App\Domains\Admin\Setting\Requests\UpdateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{ 
    public function index() //get
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $siteSettings = Setting::where('group','web')->get();
        $contentSettings = Setting::where('group','content')->get();
        $supportSettings = Setting::where('group','support')->get();
        $socialLinkSettings = Setting::where('group','social_link')->get();
        return view('Setting::index', compact('siteSettings','contentSettings', 'supportSettings','socialLinkSettings'));
    }

    public function updateSiteSetting(UpdateRequest $request, Setting $setting)
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data=$request->all();
        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                $setting = Setting::where('key', $key)->first();
                $setting_value = $value;
                if ($setting) {
                    if ($setting->type === 'image') {
                        if ($value) {
                            $uploadId = $setting->image ? $setting->image->id : null;
                            if($uploadId){
                                uploadImage($setting, $value, 'settings/images/',"setting-image", 'original', 'update', $uploadId);
                            }else{
                                uploadImage($setting, $value, 'settings/images/',"setting-image", 'original', 'save', null);
                            }
                        }
                        $setting_value = null;
                    }
                    else {
                        // Handle other fields
                        $setting->value = $setting_value;
                    }
                    $setting->save();
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => trans('messages.crud.update_record'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public  function toggleTutorChatStatus(){
        try {
            // Find setting row
            $setting = Setting::where('key', 'tutor_chat_status_cb')->first();

            if (!$setting) {
                return response()->json(['success' => false, 'error' => 'Setting not found'], 404);
            }

            // Toggle value
            $setting->value = $setting->value == 1 ? 0 : 1;
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => trans('messages.tutor_chat_status'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
