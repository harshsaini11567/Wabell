<?php

namespace App\Domains\Admin\Specialty\Controllers;

use App\Domains\Admin\Specialty\DataTables\SpecialtyRequestDataTable;
use App\Domains\Core\Specialty\Models\Specialty;
use App\Domains\Core\Specialty\Models\SpecialtyRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Domains\Core\User\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Domains\Admin\Specialty\Mail\NewSpecialtyMail;
class SpecialtyRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SpecialtyRequestDataTable $dataTable)
    {
        abort_if(Gate::denies('specialties_request_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('Specialty::SpecialtyRequest.index');
        } catch (\Exception $e) {
            // dd($e);
            abort(500);
        }
    }

    public function create($id=null)
    {
    }

    public function store(Request $request, $id=null)
    {
    }

    public function show(Request $request, $id)
    {
    } 

    public function edit(Request $request, $id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy(Request $request, $id)
    {
    }

    public function updateStatus(Request $request){
       abort_if(Gate::denies('specialties_request_status'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'id'     => [
                    'required',
                    'exists:specialty_requests,uuid',
                ],
            ]);
            if (!$validator->passes()) {
                return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()->toArray(),'message'=>'Error Occured!'],400);
            }else{
                DB::beginTransaction();
                try{
                    $specialRequest = SpecialtyRequest::where('uuid', $request->id)->first();
                    $user_info = json_decode($specialRequest->user_info, true);
                    $user_email = $user_info['user_email'] ?? null;
                    $user_name = $user_info['user_name'] ?? null;
                    $user_language = $user_info['user_language'] ?? 'en';
                    $specialRequest->update(['status' => $request['status']]);
                    if($specialRequest->status == 'accept'){
                        $newSpecialty = Specialty::create([
                            'specialty_request_id' => $specialRequest->id ? $specialRequest->id : null,
                            'name_en'     => $specialRequest->name_en ? $specialRequest->name_en : $specialRequest->name_ar,
                            'name_ar'     => $specialRequest->name_en ? $specialRequest->name_en : $specialRequest->name_ar,
                            'created_by'  => $specialRequest->created_by ?? 1
                        ]);

                        $specialRequest->notifyUsersOnAcceptance($user_email, $user_name, $user_language, $newSpecialty);

                    }
                    $pendingSpecialtyRequestCount = SpecialtyRequest::where('status', 'pending')->count();

                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.specialty_request.title_singular').' '.trans('messages.crud.status_update'),
                        'data'     => ['pending_specialty_request_count'=> $pendingSpecialtyRequestCount],
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
