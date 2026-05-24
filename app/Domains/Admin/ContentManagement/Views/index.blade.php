@extends('Layouts::app')
@section('title', $page->name_en)

@section('custom_css')
    <link href="{{ asset('admin-assets/vendor/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .tab-container .tab-content .tab-item.hide{
            display: none;
        }
        .card-footer {
            border-top: 1px solid #1E293B;
            background-color: transparent;
        }
        label .btn  {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0px;
        }
        .accordion-button {
            width: 50px;
            display: inline-flex;
            background: transparent !important;
            border: none;
            box-shadow: none !important;
            padding: 0;
            color: black !important;
        }
        .accordion-button::after {
            text-align: center;
            margin: auto !important;
            color: black !important;
        }
    </style>
@endsection

@section('main-content')
@php
    $name = "name_".app()->getLocale();
    $hasPermission = true;
    if (!Gate::check('content_management_edit')) {
        $hasPermission = false;
    }
@endphp
<div class="row">
    <div class="col-12">
        <div class="page-title-box my-md-3">
            <h4 class="page-title">{{$page->$name}}</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="page_content_main page_contet_list accordion accordion_custom" id="accordion_pageContent">
                    <form method="post" enctype="multipart/form-data" id="contentManagementform">
                        @csrf
                        @foreach ($sections as $key => $section)
                            <div class="card mb-3 accordion-item">
                                {{-- <div class="card-header">                                
                                    {{$section->$name}}
                                    <button class="accordion-button {{ $key == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#page_content_collapse_{{$section->section_key}}" aria-expanded="true" aria-controls="page_content_collapse_{{$section->section_key}}"></button>
                                </div> --}}
                                <div id="page_content_collapse_{{$section->section_key}}" class="card-body accordion-collapse collapse {{ $key == 0 ? 'show' : '' }}" aria-labelledby="page_content_header_{{$section->section_key}}" data-bs-parent="#accordion_pageContent">
                                    <div class="row">
                                        @php
                                            $editorClass = '';
                                            $colMdClass = '';
                                            if($section->section_key == 'lagal_details' || $section->section_key == 'user_register' || $section->section_key == 'partner_register' || $section->section_key == 'terms_details' || $section->section_key == 'privacy_policy_details'){
                                                $editorClass = 'tinymce-editor';
                                            }
                                        @endphp
                                        @foreach ($section->sectionMetas()->orderBy('id')->get() as $sectionMeta)
                                            @php
                                                $isHomePage = $page->slug === 'home';
                                                $isArabic = str_contains($sectionMeta->meta_key, '_ar');
                                                $isLegalSection = in_array($section->section_key, ['lagal_details', 'terms_details', 'privacy_policy_details']);
                                                $isImage = $sectionMeta->field_type === 'image';
                                                $isTextarea = $sectionMeta->field_type === 'textarea';
                                                $isTitleField = $isHomePage && str_contains($sectionMeta->meta_key, 'title_');
                                                $skipTag = $isHomePage && str_contains($sectionMeta->meta_key, 'tag_');
                                                $colSize = ($isLegalSection || $sectionMeta->meta_key == 'about_image') ? '12' : '6';
                                            @endphp

                                            @if(!$skipTag)
                                                <div class="col-md-{{ $colSize }} form-group">
                                                    <label class="form-label">{{ $sectionMeta->{'display_name_'.app()->getLocale()} }}</label>
                                                    {{-- Textarea Field --}}
                                                    @if ($isTextarea)
                                                        <textarea 
                                                            name="sections[{{ $section->id }}][{{ $sectionMeta->meta_key }}]" 
                                                            id="{{ $sectionMeta->meta_key }}"  
                                                            dir="{{ $isArabic ? 'rtl' : 'ltr' }}" 
                                                            class="form-control {{ $editorClass }}" 
                                                            rows="3"
                                                            {{ $hasPermission ? '' : 'disabled' }}
                                                        >{{ $sectionMeta->meta_value }}</textarea>

                                                    {{-- Image Field --}}
                                                    @elseif ($isImage)
                                                        <input 
                                                            type="file" 
                                                            name="sections[{{ $section->id }}][{{ $sectionMeta->meta_key }}]" 
                                                            id="image-input-{{ $sectionMeta->meta_key }}" 
                                                            class="dropify" 
                                                            data-default-file=" {{ $sectionMeta->meta_value ? asset($sectionMeta->meta_value) : '' }}"
                                                            data-show-loader="true" 
                                                            data-errors-position="outside" 
                                                            data-allowed-file-extensions="jpeg png jpg JPG PNG" 
                                                            accept="image/jpeg, image/png, image/jpg" 
                                                            {{ $hasPermission ? '' : 'disabled' }}
                                                        />

                                                    {{-- Title Field (TinyMCE for home titles) --}}
                                                    @elseif ($isTitleField)
                                                        <textarea 
                                                            name="sections[{{ $section->id }}][{{ $sectionMeta->meta_key }}]" 
                                                            id="{{ $sectionMeta->meta_key }}"  
                                                            dir="{{ $isArabic ? 'rtl' : 'ltr' }}" 
                                                            class="form-control tinymce-editor-title" 
                                                            rows="3" 
                                                            {{ $hasPermission ? '' : 'disabled' }}
                                                        >{{ $sectionMeta->meta_value }}</textarea>

                                                    {{-- Default Input Field --}}
                                                    @else
                                                        <input 
                                                            type="{{ $sectionMeta->field_type }}" 
                                                            name="sections[{{ $section->id }}][{{ $sectionMeta->meta_key }}]" 
                                                            id="{{ $sectionMeta->meta_key }}" 
                                                            class="form-control" 
                                                            dir="{{ $isArabic ? 'rtl' : 'ltr' }}" 
                                                            value="{{ $sectionMeta->meta_value }}" 
                                                            required 
                                                            {{ $hasPermission ? '' : 'disabled' }}
                                                        />
                                                    @endif
                                                </div>
                                            @endif

                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($hasPermission)
                            <div class="card-footer rounded-0 px-0 pb-0">
                                <div class="form-label justify-content-center m-0 text-end">
                                    <button type="submit" class="btn btn-success submitBtnSocailMedia">@lang('global.update')</button>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom_js')

@include('ContentManagement::partials.script')

@endsection