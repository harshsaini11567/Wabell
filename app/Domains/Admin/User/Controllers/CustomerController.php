<?php

namespace App\Domains\Admin\User\Controllers;

use App\Domains\Admin\User\DataTables\CustomerDataTable;
use App\Domains\Core\User\Models\User;
use App\Domains\Core\User\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Domains\Api\Auth\Emails\UserBanStatusMail;
class CustomerController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(CustomerDataTable $dataTable)
    {
        abort_if(Gate::denies('customer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('User::customer.index');
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    public function create(Request $request)
    {
    }

    public function store()
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        abort_if(Gate::denies('customer_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $customer = User::with(['roles'])->where('uuid', $id)->first();
             
                $viewHTML = view('User::customer.show', compact('customer'))->render();
                return response()->json(array('success' => true, 'htmlView'=>$viewHTML));
            }
            catch (\Exception $e) {
                // dd($e);
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function edit($id)
    {
        abort_if(Gate::denies('customer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $customer = User::where('uuid', $id)->first();

            $profileImage = null;
            if ($customer && $customer->profileImage && $customer->profileImage->file_path) {
                $filePath = $customer->profileImage->file_path;

                $fileNameArray = explode('/', $filePath);
                $fileName = end($fileNameArray);

                $MediaImage = Storage::disk('public')->exists($filePath)
                    ? asset('storage/' . $filePath)
                    : '';

                $profileImage = [
                    'id'           => $customer->profileImage->id,
                    'src'          => $MediaImage,
                    'documentType' => $customer->profileImage->extension,
                    'fileName'     => $fileName,
                ];
            }

            $viewHTML = view('User::customer.edit', compact('customer'))->render();
            return response()->json([
                'success' => true, 
                'htmlView' => $viewHTML,
                'profileImage' => $profileImage,
            ]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('customer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $customer = User::where('uuid', $id)->first();
            $inputs = $request->all();

            // Save user to database
            $customer->update(['name' => $inputs['name'],
                        'user_status' => $inputs['user_status']
                        ]);

            if($customer){         
                if($request->has('profile_image')){
                    $uploadId = null;
                    $actionType = 'save';
                    if($profileImageRecord = $customer->profileImage){
                        $uploadId = $profileImageRecord->id;
                        $actionType = 'update';
                    }
                    uploadImage($customer, $request->profile_image, 'user/profile-images',"user_profile", 'original', $actionType, $uploadId);
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        abort_if(Gate::denies('customer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $customer = User::where('uuid', $id)->first();
            DB::beginTransaction();
            try {
                if ($customer->profile_image_url) {
                    $uploadImageId = $customer->profileImage->id;
                    deleteFile($uploadImageId);
                }
                
                $customer->specialties()->sync([]);

                $customer->roles()->sync([]);

                $customer->delete();
                
                DB::commit();
                $response = [
                    'success'    => true,
                    'message'    => trans('messages.crud.delete_record'),
                ];
                return response()->json($response);
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function isBan(Request $request){
        abort_if(Gate::denies('customer_ban'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'id'     => [
                    'required',
                    'exists:users,uuid',
                ],
            ]);
            if (!$validator->passes()) {
                return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()->toArray(),'message'=>'Error Occured!'],400);
            }else{
                DB::beginTransaction();
                try{
                    $student = User::where('uuid', $request->id)->first();
                    if($student->is_ban == '0'){
                        $is_ban = '1';
                        $titleKey = 'master_banned_title';
                        $messageKey = 'master_banned_message';

                        $subject = trans('emails.user_ban_status_mail.subject',[],$student->language);
                        $supportEmail = getSetting('support_email');
                        Mail::to($student->email)->send(new UserBanStatusMail($student->name,$student->language, $subject, $supportEmail));
                    } else {
                        $is_ban = '0';
                        $titleKey = 'master_unbanned_title';
                        $messageKey = 'master_unbanned_message';
                    }
                    $student->update(['is_ban' => $is_ban]);
                    sendUserNotification(
                        $student->id,  // student_id
                        $titleKey,   
                        $messageKey, 
                        'ban_status', 
                        null,         
                        false,
                        ['email' => getSetting('support_email')],          
                    );
                    
                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.customer.title_singular').' '.trans('messages.crud.status_update'),
                    ];
                    return response()->json($response);
                } catch (\Exception $e) {
                    DB::rollBack();
                    // dd($e);
                    return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
                }
            }
        }
    }

    public function changeStatus(Request $request){   // active inactive
        abort_if(Gate::denies('customer_status'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'id'     => [
                    'required',
                    'exists:users,uuid',
                ],
            ]);
            if (!$validator->passes()) {
                return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()->toArray(),'message'=>'Error Occured!'],400);
            }else{
                DB::beginTransaction();
                try{
                    $customer = User::where('uuid', $request->id)->first();
                    if($customer->user_status == 'inactive'){
                        $status = 'active';
                    } else {
                        $status = 'inactive';
                    }
                    $customer->update(['user_status' => $status]);
                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.customer.title_singular').' '.trans('messages.crud.status_update'),
                    ];
                    return response()->json($response);
                } catch (\Exception $e) {
                    DB::rollBack();
                    // dd($e);
                    return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
                }
            }
        }
    }
}
