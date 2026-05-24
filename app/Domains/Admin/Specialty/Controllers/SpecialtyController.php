<?php

namespace App\Domains\Admin\Specialty\Controllers;

use App\Domains\Core\Specialty\Models\Specialty;
use App\Domains\Core\Specialty\Models\SpecialtyRequest;
use App\Domains\Admin\Specialty\Requests\SpecialtyStoreRequest;
use App\Domains\Admin\Specialty\Requests\SpecialtyUpdateRequest;
use App\Domains\Core\Specialty\Services\SpecialtyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
class SpecialtyController extends Controller
{

    protected $specialtyService;

    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('specialties_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $specialtyLevel = 0;
            $specialties = Specialty::where('specialty_status', 'active')->whereNull('parent_specialty_id')->orderBy('created_at', 'desc')->paginate(50);  // 50 per page
            
            if ($request->ajax()) {
                return view('Specialty::partials.specialty-list', compact('specialties', 'specialtyLevel'))->render();
            }

            return view('Specialty::index', compact('specialties', 'specialtyLevel'));
        } catch (\Exception $e) {
            abort(500);
        }
    }

    public function create($id=null)
    {
        abort_if(Gate::denies('specialties_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $viewHTML = view('Specialty::create', compact('id'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function store(SpecialtyStoreRequest $request, $id=null)
    {
        // dd($request->file('specialty_icon'));
        abort_if(Gate::denies('specialties_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $specialRequest = SpecialtyRequest::where(function ($query) use ($request) {
                $query->where('name_en', $request->name_en)
                      ->orWhere('name_ar', $request->name_en);
            })
            ->where('status', 'pending')
            ->first();
            
            $specialty = $this->specialtyService->createSpecialty($request->all(), $id);
            if($request->hasFile('specialty_icon')){
                $uploadId = null;
                $actionType = 'save';

                if($specialtyIconRecord = $specialty->specialtyIcon){
                    $uploadId = $specialtyIconRecord->id;
                    $actionType = 'update';
                }
                uploadImage($specialty, $request->specialty_icon, 'specialty/specialty-icon',"specialty_icon", 'original', $actionType, $uploadId);
            }
            if($specialRequest){
                $specialRequest->status = 'accept';
                $specialRequest->save();

                $specialty->specialty_request_id = $specialRequest->id;
                $specialty->save();
                $newSpecialty = $specialty;
                $user_info = json_decode($specialRequest->user_info, true);
                $user_email = $user_info['user_email'] ?? null;
                $user_name = $user_info['user_name'] ?? null;
                $user_language = $user_info['user_language'] ?? 'en';
                $specialRequest->notifyUsersOnAcceptance($user_email, $user_name, $user_language, $newSpecialty);
            }
            DB::commit();

            $specialty = specialty::where('id', $specialty->id)->first();

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
    // public function show(Request $request, $id)
    // {
    //     abort_if(Gate::denies('specialties_view'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    //     if($request->ajax()) {
    //         try{
    //             $specialty = Specialty::where('uuid', $id)->first();
    //             $viewHTML = view('Specialty::show', compact('specialty'))->render();
    //             return response()->json(array('success' => true, 'htmlView'=>$viewHTML));
    //         }
    //         catch (\Exception $e) {
    //             // dd($e);
    //             return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    //         }
    //     }
    //     return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    // } 

    public function edit(Request $request, $id)
    {
        abort_if(Gate::denies('specialties_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $specialty = Specialty::where('uuid', $id)->first();
            $viewHTML = view('Specialty::edit', compact('specialty'))->render();
            return response()->json(['success' => true, 'htmlView' => $viewHTML]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(SpecialtyUpdateRequest $request, $id)
    {
        abort_if(Gate::denies('specialties_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $specialty = Specialty::where('uuid', $id)->first();
            $this->specialtyService->updateSpecialty($specialty, $request->all());
             if($request->hasFile('specialty_icon')){
                $uploadId = null;
                $actionType = 'save';

                if($specialtyIconRecord = $specialty->specialtyIcon){
                    $uploadId = $specialtyIconRecord->id;
                    $actionType = 'update';
                }
                uploadImage($specialty, $request->specialty_icon, 'specialty/specialty-icon',"specialty_icon", 'original', $actionType, $uploadId);
            }
            $specialty = specialty::where('id', $specialty->id)->first();

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
        abort_if(Gate::denies('specialties_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $specialty = Specialty::where('uuid', $id)->first();

                if ($specialty->childSpecialties()->exists()) {
                    return response()->json([
                        'success' => false,
                        'error_type' => 'has_tasks',
                        'error' => trans('messages.has_scpeciality_error'),
                    ], 400);
                }
                $specialty->delete();
                
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

    public function getChildSpecialties($id){
        try {
            $specialty = Specialty::where('uuid', $id)->first();

            $specialtyLevel = $specialty->level;

            $specialtyCount = $specialty->childSpecialties()->count();
            $specialties = $specialty->childSpecialties;

            // dd($specialties);
            $viewHTML = view('Specialty::partials.specialty-list', compact('specialties', 'specialtyLevel'))->render();

            return response()->json([
                'success' => true,
                'specialty_count' => $specialtyCount,
                'level' => $specialtyLevel,
                'viewHTML' => $viewHTML
            ], 200);

        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function removeSpecialtyIcon(Request $request){
        $specialty = Specialty::where('uuid', $request->specialtyId)->firstOrFail();
        if($request->ajax()){
            DB::beginTransaction();
            try {
                
                $specialtyIcon = $specialty->specialtyIcon;
                if($specialtyIcon && isset($specialtyIcon->id)){
                    deleteFile($specialtyIcon->id);

                    DB::commit();
                    $data = [
                        'success' => true,
                        'specialty_icon' => asset(config('constant.default.no_image')),
                        'specialty_name' => $specialty->name_en,
                        'message' => __('messages.crud.image_removed'),
                    ];
                    return response()->json($data);
                } else {
                    return response()->json(['success' => false, 'error' => __('messages.crud.remove_image_not_found')], 400 );
                }
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => __('messages.error_message')], 400 );
            }
        } 
    }

    public function importCsv(Request $request){
        try {
            $request->validate([
                'csv_file' => 'required|mimes:csv',
            ]);
            $file = $request->file('csv_file');
            $handle = fopen($file, 'r');

            $header = fgetcsv($handle); // get header row (first row in file)

            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row); // match header with row values
                // dd($data);
                $parentId = null;

                
                if (!empty($data['Parent Specialty Name English'])) {
                    $parent = Specialty::updateOrCreate(
                        ['name_en' => $data['Parent Specialty Name English']], // lookup condition (unique)
                        [
                            'name_ar' => $data['Parent Specialty Name Arabic'] ?? $data['Parent Specialty Name English'], // fallback value
                            'status'  => 'active',
                        ]
                    );
                    $parentId = $parent->id;
                }

                
                $specialty = Specialty::updateOrCreate(
                    [
                        'name_en' => $data['Specialty Name English'],
                        'parent_specialty_id' => $parentId,
                    ], 
                    [
                        'name_ar'             => $data['Specialty Name Arabic'] ?? $data['Parent Specialty Name English'],
                        // 'parent_specialty_id' => $parentId,
                        'status'              => 'active',
                    ]
                );
                
                if (!empty($data['Icon Link'])) {
                    // Either update existing record or create new one
                    try {
                        $iconUrl = $data['Icon Link'];

                        // Generate file name from URL
                        $fileName = basename(parse_url($iconUrl, PHP_URL_PATH));
                        if (!pathinfo($fileName, PATHINFO_EXTENSION)) {
                            $fileName .= '.png'; // fallback extension
                        }

                        // Storage path
                        $storagePath = 'specialties/' . $fileName;
                        $fullPath    = storage_path('app/public/' . $storagePath);
                        $dir = dirname($fullPath);
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        // Download & Save
                        $fileContent = @file_get_contents($iconUrl);
                        if ($fileContent !== false) {
                            file_put_contents($fullPath, $fileContent);

                            // Save relation
                            $specialty->specialtyIcon()->updateOrCreate(
                                ['type' => 'specialty_icon'],
                                [
                                    'file_path' => $storagePath,
                                    'type'      => 'specialty_icon',
                                ]
                            );
                        } else {
                            Log::warning("⚠️ Could not download icon from: {$iconUrl}");
                        }
                    } catch (\Exception $e) {
                        Log::error("❌ Icon import failed for {$data['header1']}: " . $e->getMessage());
                    }
                }
            }

            fclose($handle);
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

    public function exportExcel(){
        $specialties = Specialty::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'Specialty Name English')
            ->setCellValue('B1', 'Specialty Name Arabic')
            ->setCellValue('C1', 'Parent Specialty Name English')
            ->setCellValue('D1', 'Parent Specialty Name Arabic')
            ->setCellValue('E1', 'Icon Link');
         $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        // Data
        $row = 2;
        foreach ($specialties as $specialty) {
            $parentNameEn = $specialty->parent_specialty_id 
                        ? optional(Specialty::find($specialty->parent_specialty_id))->name_en 
                        : '';
            $parentNameAr = $specialty->parent_specialty_id 
                        ? optional(Specialty::find($specialty->parent_specialty_id))->name_ar 
                        : '';
            $sheet->setCellValue('A' . $row, $specialty->name_en)
                ->setCellValue('B' . $row, $specialty->name_ar)
                ->setCellValue('C' . $row, $parentNameEn)
                ->setCellValue('D' . $row, $parentNameAr)
                ->setCellValue('E' . $row, $specialty->specialty_icon_url);
            $row++;
        }

        $filename = 'specialties_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Stream to browser
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment;filename=\"{$filename}\"");
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }
}
