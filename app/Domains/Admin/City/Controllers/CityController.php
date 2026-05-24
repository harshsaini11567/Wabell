<?php

namespace App\Domains\Admin\City\Controllers;

use App\Domains\Admin\City\DataTables\CityDataTable;
use App\Domains\Core\City\Models\City;
use App\Domains\Core\User\Models\User;
use App\Domains\Core\City\Models\Neighborhood;
use App\Domains\Admin\City\Requests\CityStoreRequest;
use App\Domains\Admin\City\Requests\CityUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Domains\Admin\City\DataTables\UsersWithoutLocationDataTable;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index(CityDataTable $dataTable)
    {
        abort_if(Gate::denies('city_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $cityPendingCount = User::where(function ($query) {
                $query->where('city_id', '0')
                ->where('deleted_at',NULL);
            })->count();

            $neighborPendingCount = User::where(function ($query) {
                $query->where('neighborhood_id', '0')
                ->where('deleted_at',NULL);
            })->count();

            return $dataTable->render('City::index', compact('cityPendingCount','neighborPendingCount'));
        } catch (\Exception $e) {
            abort(500);
        }
    }

    public function userWithoutLocation(UsersWithoutLocationDataTable $dataTable)
    {
        abort_if(Gate::denies('city_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $type = request('type', 'city');

        try {
           return $dataTable->setType($type)
                         ->render('City::users_without_location_table');
        } catch (\Exception $e) {
            abort(500);
        }
    }

    public function showUser(Request $request, string $uuid)
    {
        $user = User::with(['roles', 'city', 'neighborhood', 'specialties'])->where('uuid', $uuid)->firstOrFail();

        if ($user->hasRole('Learner')) {
            abort_if(Gate::denies('customer_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        } elseif ($user->hasRole('Master')) {
            abort_if(Gate::denies('master_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        } else {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            try {
                if ($user->hasRole('Learner')) {
                    $viewHTML = view('City::partials.customer-show', compact('user'))->render();
                } elseif ($user->hasRole('Master')) {
                    $viewHTML = view('City::partials.master-show', compact('user'))->render();
                }

                return response()->json([
                    'success' => true,
                    'htmlView' => $viewHTML
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error_type' => 'something_error',
                    'error' => $e->getMessage()
                ], 400);
            }
        }
    }

    public function create()
    {
        abort_if(Gate::denies('city_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $viewHTML = view('City::create')->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function store(CityStoreRequest $request)
    {
        abort_if(Gate::denies('city_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            // $apiKey = config('services.google.key');
            // $radiusInMeters = 30000;
            $lat = $request->lat;
            $lng = $request->lng;
            
            // $existingCity = City::where('name_en', $request->name_en)
            // ->orWhere(function ($q) use ($lat, $lng) {
            //     $q->where('lat', $lat)->where('lng', $lng);
            // })
            // ->first();
            // if ($existingCity) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "City already exists!"
            //     ], 400);
            // }

            $city = City::create([
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
                'lat' => $lat,
                'lng' => $lng,
                'created_by' => auth('web')->id(),
            ]);
            // $fetchPlaces = function ($lat, $lng, $radiusInMeters, $language, $apiKey, $cityName) {
            //     $places = [];
            //     $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=sublocality+in+". urlencode($cityName) ."&key=$apiKey&language=$language";
            //     // dd($url);
            //     do {
            //         $data = json_decode(file_get_contents($url), true);

            //         if (!empty($data['results'])) {
            //             foreach ($data['results'] as $place) {
            //                $places[] = [
            //                     'name' => $place['name'],
            //                     'lat' => $place['geometry']['location']['lat'],
            //                     'lng' => $place['geometry']['location']['lng'],
            //                 ];
            //             }
            //         }
            //         // handle pagination
            //         if (isset($data['next_page_token'])) {
            //             sleep(2); // required by Google
            //             $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?pagetoken=" . $data['next_page_token'] . "&key=$apiKey&language=$language";
            //         } else {
            //             $url = null;
            //         }
            //     } while ($url);
            //     return $places;
            // };

            
            // ✅ Fetch EN + AR neighborhoods
            // $placesEn = $fetchPlaces($lat, $lng, $radiusInMeters, 'en', $apiKey, $city->name_en);
            // $placesAr = $fetchPlaces($lat, $lng, $radiusInMeters, 'ar', $apiKey, $city->name_en);
            // ✅ Merge AR names into EN list
            // foreach ($placesEn as $i => &$placeEn) {
            //     $placeAr = $placesAr[$i] ?? null;
            //     if ($placeAr) {
            //         $placeEn['name_ar'] = $placeAr['name'];
            //     } else {
            //         $placeEn['name_ar'] = null;
            //     }
            // }
            // unset($placeEn);

            // ✅ Store neighborhoods without duplicates
            // foreach ($placesEn as $n) {
            //     $exists = Neighborhood::where('city_id', $city->id)
            //         ->where('lat', $n['lat'])
            //         ->where('lng', $n['lng'])
            //         ->exists();

            //     if (!$exists) {
            //         Neighborhood::create([
            //             'city_id' => $city->id,
            //             'name_en' => $n['name'] ?? null,
            //             'name_ar' => $n['name_ar'] ?? null,
            //             'lat' => $n['lat'],
            //             'lng' => $n['lng'],
            //         ]);
            //     }
            // }
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
    public function show(Request $request, $id)
    {
        abort_if(Gate::denies('city_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $city = City::with('neighborhoods')->where('uuid', $id)->first();
                $viewHTML = view('City::show', compact('city'))->render();
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
        abort_if(Gate::denies('city_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $city = City::where('uuid', $id)->first();
            $viewHTML = view('City::edit', compact('city'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(CityUpdateRequest $request, City $city)
    {
        abort_if(Gate::denies('city_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $input = $request->only('name_en', 'name_ar', 'lat', 'lng');
            $city->update($input);
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
        abort_if(Gate::denies('city_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $city = City::where('uuid', $id)->first();
                if ($city->neighborhoods()->exists()) {
                    return response()->json([
                        'success' => false,
                        'error_type' => 'has_neighborhood',
                        'error' => trans('messages.has_neighborhoods_error'),
                    ], 400);
                }
                $city->delete();
                
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

    public function importCsv(Request $request){
        try {
            $request->validate([
                'csv_file' => 'required|mimes:csv',
            ]);
            $file = $request->file('csv_file');
            $handle = fopen($file, 'r');

            $header = fgetcsv($handle); // get header row (first row in file)
            $header = array_map('trim', $header);
            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $row = array_map('trim', $row);
                $data = array_combine($header, $row); // match header with row values
                // dd($data);
                if (empty($data['name_en'])) {
                    continue; // skip empty rows
                }
                if (empty($data['city'])) {
                    City::updateOrCreate(
                        ['name_en' => $data['name_en']],
                        [
                            'name_ar' => $data['name_ar'] ?? $data['name_en'],
                            'lat'     => $data['lat'] ?? null,
                            'lng'     => $data['lng'] ?? null,
                            'status'  => 'active',
                        ]
                    );
                } 
                else {
                    $city = City::where('name_en', $data['city'])->first();

                    if ($city) {
                        Neighborhood::updateOrCreate(
                            [
                                'name_en' => $data['name_en'],
                                'city_id' => $city->id,
                            ],
                            [
                                'name_ar' => $data['name_ar'] ?? $data['name_en'],
                                'lat'     => $data['lat'] ?? null,
                                'lng'     => $data['lng'] ?? null,
                                'status'  => 'active',
                            ]
                        );
                    } else {
                        // if city not found, optionally log or skip
                        Log::warning('City not found for neighborhood: '.$data['name_en']);
                    }
                }
            }

            fclose($handle);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => trans('messages.csv_import'),
            ], 200);
        }
        catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => __('messages.error_message')], 400 );
        }
    }
}
