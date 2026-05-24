@extends('Layouts::app')
@section('title', __('cruds.setting.title'))

@section('custom_css')
    <link href="{{ asset('admin-assets/vendor/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .tab-container .tab-content .tab-item.hide{
            display: none;
        }
        .form-group{
            margin: 0px;
        }
        .card-footer {
            border-top: 1px solid #D4D4EA;
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
    </style>
@endsection

@section('main-content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box mb-3 mt-2">
                <h4 class="page-title">@lang('cruds.setting.title')</h4>
            </div>
            <div class="card">
                <div class="tab-container setting_page_data">
                    <nav class="tab-nav">
                        <ul class="nav nav-underline nav-justified gap-0">
                            <li class="nav-item"><button class="nav-link setting-tab active" id="site-setting">@lang('cruds.setting.site')</button></li>
                            <li class="nav-item"><button class="nav-link setting-tab" id="content-setting">@lang('cruds.setting.content')</button></li>
                            <li class="nav-item"><button class="nav-link setting-tab" id="support-setting">@lang('cruds.setting.support')</button></li>
                            <li class="nav-item"><button class="nav-link setting-tab" id="social-link-setting">@lang('cruds.setting.social_link')</button></li>
                        </ul>
                    </nav>
                    <div class="tab-content p-sm-4 p-3">
                        <div class="tab-item" data-id="site-setting">
                            <h3>@lang('cruds.setting.site_setting')</h3>
                            <form class="msg-form" id="siteSettingform" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="setting_type" value="site">
                                <div class="card-body px-0 pb-0">
                                    <div class="row">
                                        @foreach($siteSettings as $key => $siteSetting)
                                            @if($siteSetting->type == 'text' || $siteSetting->type == 'textarea')
                                            <div class="mb-3 col-lg-12">
                                                <div class="form-group">
                                                    <label class="form-label">{{ $siteSetting->display_name}} <span class="required">*</span></label>
                                                    <input type="text" class="form-control" value="{{$siteSetting->value}}" name="{{$siteSetting->key}}" required/>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            @if($siteSetting->type == 'image')
                                                @php
                                                    $imageUrl = $siteSetting->image_url;
                                                    if(empty($imageUrl) && $siteSetting->key == 'favicon'){
                                                        $imageUrl = asset(config('constant.default.favicon'));
                                                    } else if(empty($imageUrl) && $siteSetting->key == 'site_logo'){
                                                        $imageUrl = asset(config('constant.default.logo'));
                                                    } else if(empty($imageUrl) && $siteSetting->key == 'auth_logo'){
                                                        $imageUrl = asset(config('constant.default.auth_logo'));
                                                    }
                                                @endphp
                                                <div class="mb-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ $siteSetting->display_name}} </label>
                                                        <input name="{{$siteSetting->key}}" type="file" class="dropify" id="image-input-{{$siteSetting->key}}" data-default-file=" {{ $imageUrl }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg PNG JPG" accept="image/jpeg, image/png, image/jpg, image/PNG, image/JPG" />
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer rounded-0 px-0 pb-0">
                                    <div class="form-label justify-content-center m-0 text-end">
                                        <button type="submit" class="btn btn-success submitBtn">@lang('global.update')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-item hide" data-id="content-setting">
                            <h3>@lang('cruds.setting.content_setting')</h3>
                            <div class="msg-form" id="contentSettingform" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="setting_type" value="content">
                                <div class="card-body px-0 pb-0">
                                    <div class="row">
                                        @foreach($contentSettings as $key => $contentSetting)                                
                                            @if($contentSetting->type == 'text')
                                                <div class="mb-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ $contentSetting->display_name}} <span class="required">*</span></label>
                                                        <textarea class="tinymce-editor" id="{{$contentSetting->key}}" name="{{$contentSetting->key}}" required 
                                                            data-dir="{{ strpos($contentSetting->key, 'ar') !== false ? "rtl" : 'ltr' }}" 
                                                            >{{$contentSetting->value}}</textarea>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($contentSetting->type == 'image')
                                                @php
                                                    $imageUrl = $contentSetting->image_url;
                                                    if(empty($imageUrl) && $contentSetting->key == 'learner_welcome_video'){
                                                        $imageUrl = asset(config('constant.default.learner_welcome_video'));
                                                    } else if(empty($imageUrl) && $contentSetting->key == 'master_welcome_video'){
                                                        $imageUrl = asset(config('constant.default.master_welcome_video'));
                                                    }
                                                @endphp
                                                <div class="mb-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label class="form-label d-flex justify-content-between align-items-center">
                                                            {{ $contentSetting->display_name}} 
                                                            <a href="{{ $imageUrl }}" class="btn btn-outline-primary btn-sm pdf-view" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Show"><i class="ri-eye-line"></i></a>
                                                        </label>
                                                        <input name="{{$contentSetting->key}}" type="file" class="dropify" id="image-input-{{$contentSetting->key}}" data-default-file=" {{ $imageUrl }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="mp4 mov avi webm" accept="video/mp4, video/quicktime, video/x-msvideo, video/webm" />
                                                    </div>
                                                </div>
                                            @endif                               
                                        @endforeach   
                                    </div>
                                </div>
                                <div class="card-footer rounded-0 px-0 pb-0">
                                    <div class="form-label justify-content-center m-0 text-end">
                                        <button type="submit" class="btn btn-success submitBtnContent">@lang('global.update')</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-item hide" data-id="support-setting">
                            <h3>@lang('cruds.setting.support_setting')</h3>
                            <form class="msg-form" id="supportSettingform" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="setting_type" value="support">
                                <div class="card-body px-0 pb-0">
                                    <div class="row">
                                        @foreach($supportSettings as $key => $supportSetting)
                                            @if($supportSetting->type == 'text')
                                            <div class="mb-3 col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-label">{{ $supportSetting->display_name}} <span class="required">*</span></label>
                                                    <input type="text" class="form-control" value="{{$supportSetting->value}}" name="{{$supportSetting->key}}" required/>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer rounded-0 px-0 pb-0">
                                    <div class="form-label justify-content-center m-0 text-end">
                                        <button type="submit" class="btn btn-success submitBtn">@lang('global.update')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-item hide" data-id="social-link-setting">
                            <h3>@lang('cruds.setting.social_link_setting')</h3>
                            <form class="msg-form" id="socialLinkSettingform" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="setting_type" value="social_link">
                                <div class="card-body px-0 pb-0">
                                    <div class="row">
                                        @foreach($socialLinkSettings as $key => $socialLinkSetting)
                                            @if($socialLinkSetting->type == 'text')
                                            <div class="mb-3 col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-label">{{ $socialLinkSetting->display_name}}</label>
                                                    <input type="text" class="form-control" value="{{$socialLinkSetting->value}}" name="{{$socialLinkSetting->key}}"/>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer rounded-0 px-0 pb-0">
                                    <div class="form-label justify-content-center m-0 text-end">
                                        <button type="submit" class="btn btn-success submitBtn">@lang('global.update')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   


@endsection

@section('custom_js')

@include('Setting::partials.script')

@endsection