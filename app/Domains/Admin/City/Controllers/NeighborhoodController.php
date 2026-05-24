<?php

namespace App\Domains\Admin\City\Controllers;

use App\Domains\Admin\City\DataTables\NeighborhoodDataTable;
use App\Domains\Core\City\Models\Neighborhood;
use App\Domains\Core\City\Models\City;
use App\Domains\Admin\City\Requests\NeighborhoodStoreRequest;
use App\Domains\Admin\City\Requests\NeighborhoodUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class NeighborhoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(NeighborhoodDataTable $dataTable, $city)
    {
        abort_if(Gate::denies('city_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $city = City::with('neighborhoods')->select('id','uuid','name_en')->where('uuid',$city)->firstOrFail();
            // $neighborhoods   = $city->neighborhoods;
            $dataTable->setCity($city);
            return $dataTable->/* with('neighborhoods', $neighborhoods)-> */render('City::Neighborhood.index', ['city' => $city]);
        } catch (\Exception $e) {
            // dd($e);
            abort(500);
        }
    }

    public function create($city)
    {
        abort_if(Gate::denies('city_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $city = City::where('uuid',$city)->first();
            $viewHTML = view('City::Neighborhood.create',compact('city'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function store(NeighborhoodStoreRequest $request, $city)
    {
        abort_if(Gate::denies('city_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $city = City::where('uuid',$city)->first();
            $input = $request->only('name_en', 'name_ar', 'lat', 'lng');
            $input['city_id']= $city->id;
            $input['created_by'] = auth('web')->id();
            Neighborhood::create($input);

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
    public function show(Request $request, $id, $neighborhood)
    {
        abort_if(Gate::denies('city_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $neighborhood = Neighborhood::where('uuid', $neighborhood)->first();
                $viewHTML = view('City::Neighborhood.show', compact('neighborhood'))->render();
                return response()->json(array('success' => true, 'htmlView'=>$viewHTML));
            }
            catch (\Exception $e) {
                // dd($e);
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function edit(Request $request, $city,$neighborhood)
    {
        abort_if(Gate::denies('city_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $city = City::where('uuid',$city)->first();
            $neighborhood = Neighborhood::where('uuid', $neighborhood)->first();
            $viewHTML = view('City::Neighborhood.edit', compact('neighborhood','city'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(NeighborhoodUpdateRequest $request, City $city, Neighborhood $neighborhood)
    {
        abort_if(Gate::denies('city_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $input = $request->only('name_en', 'name_ar', 'lat', 'lng');
            $neighborhood->update($input);
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
    public function destroy(Request $request, $city,$neighborhood)
    {
        abort_if(Gate::denies('city_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $neighborhood = Neighborhood::where('uuid', $neighborhood)->first();
                $neighborhood->delete();
                
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

    public function getNeighborhoodByNeighborhood(Request $request){

    }
}
