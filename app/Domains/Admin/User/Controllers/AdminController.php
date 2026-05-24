<?php

namespace App\Domains\Admin\User\Controllers;

use App\Domains\Admin\User\DataTables\AdminDataTable;
use App\Domains\Core\User\Models\User;
use App\Domains\Admin\User\Requests\AdminStoreRequest;
use App\Domains\Admin\User\Requests\AdminUpdateRequest;
use App\Domains\Core\Role\Models\Role;
use App\Domains\Core\User\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(AdminDataTable $dataTable)
    {
        abort_if(Gate::denies('admin_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('User::admin.index');
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('admin_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $roles = Role::whereNotIn('role_type', ['app','super_admin'])->where('role_status', 'active')->get();
            $viewHTML = view('User::admin.create', compact('roles'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function store(AdminStoreRequest $request)
    {
        abort_if(Gate::denies('admin_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $this->userService->createUpdateAdmin($request);            

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans('messages.crud.add_record'),
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        abort_if(Gate::denies('admin_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $admin = User::with(['roles'])->where('uuid', $id)->first();
             
                $viewHTML = view('User::admin.show', compact('admin'))->render();
                return response()->json(array('success' => true, 'htmlView'=>$viewHTML));
            }
            catch (\Exception $e) {
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function edit(Request $request, $id)
    {
        abort_if(Gate::denies('admin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $admin = User::where('uuid', $id)->first();
            $roles = Role::whereNotIn('role_type', ['app','super_admin'])->where('role_status', 'active')->get();
            $viewHTML = view('User::admin.edit', compact('admin', 'roles'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(AdminUpdateRequest $request, $id)
    {
        abort_if(Gate::denies('admin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $admin = User::where('uuid', $id)->first();
            $this->userService->createUpdateAdmin($request, $admin);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans('messages.crud.update_record'),
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        abort_if(Gate::denies('admin_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $admin = User::where('uuid', $id)->first();
            DB::beginTransaction();
            try {
                if ($admin->profile_image_url) {
                    $uploadImageId = $admin->profileImage->id;
                    deleteFile($uploadImageId);
                }
                $admin->roles()->sync([]);

                $admin->delete();
                
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

    public function changeStatus(Request $request){
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
                    $admin = User::where('uuid', $request->id)->first();
                    if($admin->user_status == 'inactive'){
                        $status = 'active';
                    } else {
                        $status = 'inactive';
                    }
                    $admin->update(['user_status' => $status]);

                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.admin.title_singular').' '.trans('messages.crud.status_update'),
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

    public function changePassword(Request $request, $id)
    {
        try {
            $viewHTML = view('User::admin.partials.change-password', compact('id'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function changePasswordSubmit(Request $request, $id){
        $request->validate([
            'password'  => ['required', 'string', 'min:8','confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/'],
        ], [
            'password.regex' => trans('validation.password.regex',['attribute'=> trans('cruds.api.password')]),
        ]);        
        if ($request->ajax()) {
            $admin = User::where('uuid', $id)->first();
            DB::beginTransaction();
            try {
                $input = $request->only('password');

                $input['password'] = Hash::make($request->password);

                $admin->update($input);
                
                DB::commit();
                $response = [
                    'success'    => true,
                    'message'    => trans('messages.password_updated_successfully'),
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
}
