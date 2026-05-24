@extends('Layouts::app')
@section('title', __('cruds.splash_screen.title'))

@section('custom_css')
<style>
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
<link href="{{ asset('admin-assets/vendor/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/vendor/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('main-content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between items-center">
            <div class="page-title-box my-3">
                 <h4 class="page-title">
                        @lang('cruds.menus.splashScreen')
                </h4>
            </div>
            <!-- <div class="my-3">
                @if($splashScreens->count() < 10)
                <a href="javascript:void(0);"  class="btn btn-primary btnAddSplashScreen" data-create-url="{{ route('splash-screens.create') }}">Create</a>
                @endif
            </div> -->
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="faq_main faq_list accordion" id="accordion_faq">
                        @if($splashScreens->count() > 0)
                            @include('SplashScreen::partials.splash-screen-list', ['splashScreens' => $splashScreens])
                        @else
                            <div class="row faq_main_row">
                                <div class="col-md-12 faq_row">
                                    @lang('messages.no_record_found')
                                </div>
                            </div>
                        @endif
                    </div>
                    <div id="loading" style="display:none;text-align:center;">
                        <img src="default/data_loader.gif" style="width: 50px;" alt="loader" />
                    </div>
                </div>
            </div> 
        </div> 
    </div>
   


@endsection

@section('custom_js')

@include('SplashScreen::partials.script')

@endsection