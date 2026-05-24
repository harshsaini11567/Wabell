@extends('Layouts::app')
@section('title', __('cruds.faq.title'))

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
@endsection

@section('main-content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between items-center">
            <div class="page-title-box">
                 <h4 class="page-title">
                    @if (Request::is('faqs'))
                        @lang('cruds.menus.faq')
                    @elseif (Request::is('master-faqs'))
                        @lang('cruds.menus.masterFaq')
                    @elseif (Request::is('web-faqs'))
                        @lang('cruds.menus.web_faq')
                    @endif
                </h4>
            </div>
            <div class="my-3">
                @php
                    $createUrl = route('faqs.create');
                    if(request()->routeIs('master-faqs.*')){
                        $createUrl = route('master-faqs.create');
                    } else if(request()->routeIs('web-faqs.*')){
                        $createUrl = route('web-faqs.create');
                    }
                @endphp
                <a href="javascript:void(0);"  class="btn btn-primary btnAddFaq" data-create-url="{{ $createUrl }}">@lang('global.create')</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="faq_main faq_list accordion" id="accordion_faq">
                        @if($faqs->count() > 0)
                            @include('Faq::partials.faq-list', ['faqs' => $faqs])
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

@include('Faq::partials.script')

@endsection