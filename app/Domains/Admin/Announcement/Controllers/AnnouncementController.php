<?php

namespace App\Domains\Admin\Announcement\Controllers;

use App\Domains\Admin\Announcement\DataTables\AnnouncementDataTable;
use App\Domains\Core\Announcement\Models\Announcement;
use App\Domains\Core\Permission\Models\Permission;
use App\Domains\Core\User\Models\User;
use App\Domains\Admin\Announcement\Requests\AnnouncementStoreRequest;
use App\Domains\Admin\Announcement\Requests\AnnouncementUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AnnouncementDataTable $dataTable)
    {
        abort_if(Gate::denies('announcement_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('Announcement::index');
        } catch (\Exception $e) {
            // dd($e);
            abort(500);
        }
    }

    public function create()
    {
        abort_if(Gate::denies('announcement_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $viewHTML = view('Announcement::create')->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function store(AnnouncementStoreRequest $request)
    {
        abort_if(Gate::denies('announcement_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $input = $request->only('title_en', 'title_ar', 'description_en', 'description_ar');
            $announcement = Announcement::create($input);
            $masters = User::whereHas('roles', function ($q) {
                $q->where('role_type', 'app')
                ->where('name_en', 'Master');
            })->get();
            foreach ($masters as $master) {

                $title = [
                    'en' => $announcement->title_en,
                    'ar' => $announcement->title_ar,
                ];
                $description = [
                    'en' => $announcement->description_en,
                    'ar' => $announcement->description_ar,
                ];

                sendUserNotification(
                    $master->id,
                    $title,
                    $description,
                    'announcement',            
                );
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans('messages.crud.add_record'),
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        abort_if(Gate::denies('announcement_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $announcement = Announcement::where('uuid', $id)->firstOrFail();
                $viewHTML = view('Announcement::show', compact('announcement'))->render();
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
        
    }

    public function update( )
    {
       
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        abort_if(Gate::denies('announcement_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $announcement = Announcement::where('uuid', $id)->first();
                $announcement->delete();
                
                DB::commit();
                $response = [
                    'success'    => true,
                    'message'    => trans('messages.crud.delete_record'),
                ];
                return response()->json($response);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }
}
