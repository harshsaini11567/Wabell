<?php

namespace App\Domains\Admin\SplashScreen\Controllers;

use App\Domains\Core\SplashScreen\Models\SplashScreen;
use App\Domains\Core\Permission\Models\Permission;
use App\Domains\Admin\SplashScreen\Requests\SplashScreenStoreRequest;
use App\Domains\Admin\SplashScreen\Requests\SplashScreenUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SplashScreenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('splash_screen_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $splashScreens = SplashScreen::orderBy('position')->get();
            
            if ($request->ajax()) {
                return view('SplashScreen::partials.splash-screen-list', compact('splashScreens'))->render();
            }
            return view('SplashScreen::index', compact('splashScreens'));
        } catch (\Exception $e) {
            // dd($e);
            abort(500);
        }
    }

    public function create()
    {
        abort_if(Gate::denies('splash_screen_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $permissions = Permission::where('status', 1)
            ->where('name', 'not like', '%_access') 
            ->get();
            $groupedPermissions = $permissions->groupBy(function ($permission) {
                return explode('_', $permission->name, 2)[0];
            });
            $viewHTML = view('SplashScreen::create',compact('groupedPermissions'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function store(SplashScreenStoreRequest $request)
    {
        abort_if(Gate::denies('splash_screen_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $input = $request->only('title_en', 'title_ar', 'description_en', 'description_ar', 'status');
            $maxPosition = SplashScreen::max('position') ?? 0;
            $input['position'] = $maxPosition + 1;
            $splashScreen = SplashScreen::create($input);
            if($request->has('splash_image')){
                $uploadId = null;
                $actionType = 'save';
                uploadImage($splashScreen, $request->splash_image, 'splash/splash-images',"splash_image", 'original', $actionType, $uploadId);
            }
            
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
    public function show(Request $request, SplashScreen $splashScreen)
    {
        abort_if(Gate::denies('splash_screen_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $viewHTML = view('SplashScreen::show', compact('splashScreen'))->render();
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
        abort_if(Gate::denies('splash_screen_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {

            $splashScreen = SplashScreen::where('id', $id)->first();

            $splashImage = null;
            if ($splashScreen && $splashScreen->splashImage && $splashScreen->splashImage->file_path) {
                $filePath = $splashScreen->splashImage->file_path;

                $fileNameArray = explode('/', $filePath);
                $fileName = end($fileNameArray);

                $MediaImage = Storage::disk('public')->exists($filePath)
                    ? asset('storage/' . $filePath)
                    : '';

                $splashImage = [
                    'id'           => $splashScreen->splashImage->id,
                    'src'          => $MediaImage,
                    'documentType' => $splashScreen->splashImage->extension,
                    'fileName'     => $fileName,
                ];
            }

            $viewHTML = view('SplashScreen::edit', compact('splashScreen'))->render();
            return response()->json([
                'success' => true, 
                'htmlView' => $viewHTML,
                'splashImage' => $splashImage,
            ]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(SplashScreenUpdateRequest $request, SplashScreen $splashScreen)
    {
        abort_if(Gate::denies('splash_screen_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $input = $request->only('title_en', 'title_ar', 'description_en', 'description_ar', 'status');
            if ($splashScreen->status == 'active' && $input['status'] == 'inactive') {
                $activeCount = SplashScreen::where('status', 1)->count();
                if ($activeCount == 1) {
                    return response()->json([
                        'success' => false,
                        'error_type' => 'validation_error',
                        'error' => trans('messages.active_onboarding_screen'),
                    ], 400);
                }
            }
            $splashScreen->update($input);
            // dd($splashScreen);
            if($splashScreen){         
                if($request->has('splash_image')){
                    $uploadId = null;
                    $actionType = 'save';
                    if($splashImageRecord = $splashScreen->splashImage){
                        $uploadId = $splashImageRecord->id;
                        // dd($uploadId);
                        $actionType = 'update';
                    }
                    uploadImage($splashScreen, $request->splash_image, 'splash/splash-images',"splash_image", 'original', $actionType, $uploadId);
                }
            }
            
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
    public function destroy(Request $request, SplashScreen $splashScreen)
    {
        abort_if(Gate::denies('splash_screen_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $splashScreen->delete();
                
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

    public function sort(Request $request)
    {
        foreach ($request->order as $item) {
            SplashScreen::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(['status' => 'success']);
    }

}
