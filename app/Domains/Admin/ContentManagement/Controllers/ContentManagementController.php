<?php

namespace App\Domains\Admin\ContentManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Core\Setting\Models\Setting;
use App\Domains\Core\ContentManagement\Models\Page;
use App\Domains\Core\ContentManagement\Models\SectionMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ContentManagementController extends Controller
{ 
    public function index($slug) //get
    {
        abort_if(Gate::denies('content_management_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            $page = Page::where('slug', $slug)->first();
            $sections = $page->sections()->where('status', 'active')->get();

            return view('ContentManagement::index', compact('page', 'sections'));
        } catch (\Throwable $th) {
            dd($th);
            abort(500);
        }
        
    }

    public function updatepPageContent(Request $request)
    {
        abort_if(Gate::denies('content_management_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            DB::beginTransaction();
            foreach ($request->input('sections', []) as $sectionId => $fields) {
                foreach ($fields as $key => $value) {
                    SectionMeta::updateOrCreate(
                        [
                            'section_id' => $sectionId,
                            'meta_key' => $key
                        ],
                        [
                            'meta_value' => $value,
                        ]
                    );
                }
            }

            foreach ($request->file('sections', []) as $sectionId => $files) {
                foreach ($files as $key => $file) {
                    if ($file && $file->isValid()) {
                        $filename = $file->store('content_management/images', 'public');

                        SectionMeta::updateOrCreate(
                            [
                                'section_id' => $sectionId,
                                'meta_key' => $key,
                            ],
                            [
                                'meta_value' => '/storage/' . $filename,
                            ]
                        );
                    }
                }
            }

            // Flash messages after login
            session()->flash('success', __('messages.crud.update_record'));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => trans('messages.crud.update_record'),
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $th->getmessage()], 400 );
        }
    }
}
