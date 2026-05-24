<?php

namespace App\Domains\Admin\User\Controllers;

use App\Domains\Admin\User\DataTables\MasterDataTable;
use App\Domains\Core\User\Models\User;
use App\Domains\Admin\User\Requests\MasterUpdateRequest;
use App\Domains\Core\User\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Domains\Api\Auth\Emails\UserBanStatusMail;
class MasterController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(MasterDataTable $dataTable)
    {
        abort_if(Gate::denies('master_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('User::master.index');
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    public function create(Request $request)
    {
    }

    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        abort_if(Gate::denies('master_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $master = User::with(['roles'])->where('uuid', $id)->first();
             
                $viewHTML = view('User::master.show', compact('master'))->render();
                return response()->json(array('success' => true, 'htmlView'=>$viewHTML));
            }
            catch (\Exception $e) {
                // dd($e);
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function edit(Request $request, $id)
    {
        abort_if(Gate::denies('master_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $master = User::where('uuid', $id)->with(['masterDetail'])->first();

            $preloadedIdFiles = [];
            if(isset($master->idFiles) && $master->idFiles){
                foreach($master->idFiles as $record){
                    $fileNameArray = explode('/', $record->file_path);
                    $fileName = end($fileNameArray);
                    $fileSize = File::size(public_path('storage/'.$record->file_path));
                    if($record->file_path && Storage::disk('public')->exists($record->file_path)){
                        $MediaImage = asset('storage/'.$record->file_path);
                    }else{
                        $MediaImage = '';
                    }

                    $preloadedIdFiles[]= ['id'=>$record->id,'src'=>$MediaImage,'documentType'=>$record->extension, 'fileName' => $fileName, 'size' => $fileSize];
                }
            } 

            $preloadedCertificateFiles = [];
            if(isset($master->certificateFiles) && $master->certificateFiles){
                foreach($master->certificateFiles as $record){
                    $fileNameArray = explode('/', $record->file_path);
                    $fileName = end($fileNameArray);
                    $fileSize = File::size(public_path('storage/'.$record->file_path));
                    if($record->file_path && Storage::disk('public')->exists($record->file_path)){
                        $MediaImage = asset('storage/'.$record->file_path);
                    }else{
                        $MediaImage = '';
                    }

                    $preloadedCertificateFiles[]= ['id'=>$record->id,'src'=>$MediaImage,'documentType'=>$record->extension, 'fileName' => $fileName, 'size' => $fileSize];
                }
            } 


            $viewHTML = view('User::master.edit', compact('master'))->render();
            return response()->json([
                'success' => true, 
                'htmlView' => $viewHTML, 
                'preloadedIdFiles' => $preloadedIdFiles,
                'preloadedCertificateFiles' => $preloadedCertificateFiles,
            ]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(MasterUpdateRequest $request, $id)
    {
        abort_if(Gate::denies('master_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $master = User::where('uuid', $id)->with('masterDetail')->first();
            $inputs = $request->all();

            // Save user to database
            $master->update(['name' => $inputs['name'],
                            'user_status' => $inputs['user_status']
                            ]);

            if($master){
                // upload master Details
                $master->masterDetail->update([
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
                        uploadImage($master, $file,'user/certificates/id_files', 'id_file'); 
                    }
                }
                if(isset($request->userIdFiles)){
                    $documentIds = explode(',', $request->userIdFiles);
                    foreach($documentIds as $documentId){
                        deleteFile($documentId);
                    }
                }

                // Certificates
                if($request->has('certificate_files')){
                    foreach($request->file('certificate_files') as $file)
                    {
                        uploadImage($master, $file,'user/certificates/certificate_files', 'certificate_file'); 
                    }
                }
                if(isset($request->userCertificateFiles)){
                    $documentIds = explode(',', $request->userCertificateFiles);
                    foreach($documentIds as $documentId){
                        deleteFile($documentId);
                    }
                }

                if($request->has('profile_image')){
                    $uploadId = null;
                    $actionType = 'save';
                    if($profileImageRecord = $master->profileImage){
                        $uploadId = $profileImageRecord->id;
                        $actionType = 'update';
                    }
                    uploadImage($master, $request->profile_image, 'user/profile-images',"user_profile", 'original', $actionType, $uploadId);
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
        abort_if(Gate::denies('master_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $master = User::where('uuid', $id)->first();
            DB::beginTransaction();
            try {
                if ($master->profile_image_url) {
                    $uploadImageId = $master->profileImage->id;
                    deleteFile($uploadImageId);
                }

                if($master->masterDetail){
                    $master->masterDetail()->delete();
                }

                if($master->id_files_urls > 0){
                    $idFiles = $master->idFiles;
                    foreach($idFiles as $idFile){
                        deleteFile($idFile->id);
                    }
                }

                if($master->certificate_files_urls > 0){
                    $certificateFiles = $master->certificateFiles;
                    foreach($certificateFiles as $certificateFile){
                        deleteFile($certificateFile->id);
                    }
                }
                
                $master->specialties()->sync([]);

                $master->roles()->sync([]);

                $master->delete();
                $unverifiedCount = User::whereHas('roles', function($q) {
                    $q->where('role_type', 'app')->where('name_en','Master');
                })
                ->where(function($q) {
                    $q->whereNull('date_of_birth')
                    ->orWhereDoesntHave('uploads', function($q2) {
                        $q2->where('type', 'certificate_file');
                    })
                    ->orWhereDoesntHave('specialties');
                })
                ->whereNull('deleted_at') // exclude deleted
                ->count();
                
                DB::commit();
                $response = [
                    'success'    => true,
                    'message'    => trans('messages.crud.delete_record'),
                    'data'     => ['unverified_count'=> $unverifiedCount],
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

    public function changeStatus(Request $request){   // active in-active
         abort_if(Gate::denies('master_status'), Response::HTTP_FORBIDDEN, '403 Forbidden');
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
                    $master = User::where('uuid', $request->id)->first();
                    if($master->user_status == 'inactive'){
                        $status = 'active';
                    } else {
                        $status = 'inactive';
                    }
                    $master->update(['user_status' => $status]);
                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.master.title_singular').' '.trans('messages.crud.status_update'),
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

    public function isBan(Request $request){
        abort_if(Gate::denies('master_ban'), Response::HTTP_FORBIDDEN, '403 Forbidden');
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
                    $master = User::where('uuid', $request->id)->first();
                    if($master->is_ban == 0){
                        $is_ban = 1;
                        $titleKey = 'master_banned_title';
                        $messageKey = 'master_sub_banned_message';
                        $type = 'ban_status';
                        $subject = trans('emails.user_ban_status_mail.subject',[],$master->language);
                        $supportEmail = getSetting('support_email');
                        Mail::to($master->email)->send(new UserBanStatusMail($master->name,$master->language, $subject, $supportEmail));
                    } else {
                        $is_ban = 0;
                        $titleKey = 'master_unbanned_title';
                        $messageKey = 'master_sub_unbanned_message';
                        $type = 'subscription_ban_status';
                    }
                    $master->update(['is_ban' => $is_ban]);

                    sendUserNotification(
                        $master->id,  // master_id
                        $titleKey,   
                        $messageKey, 
                        $type, 
                        null,         
                        false,
                        ['email' => getSetting('support_email')],          
                    );
                   
                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.master.title_singular').' '.trans('messages.crud.status_update'),
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

    public function isMasterApproved(Request $request){
        abort_if(Gate::denies('master_is_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');
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
                    $master = User::where('uuid', $request->id)->first();
                    $updateData = ['is_approved' => $request->isApproved];
                    if ($request->isApproved == 0) {
                        $updateData['user_status'] = 'inactive';
                    }
                    else if($request->isApproved == 1) {
                        $updateData['user_status'] = 'active';
                    }

                    $master->update($updateData);
                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.master.title_singular').' '.trans('messages.crud.status_update'),
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
