@extends('Layouts::app')
@section('title', __('cruds.specialty.title'))

@section('custom_css')
<link href="{{ asset('admin-assets/vendor/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

<style>
    .specialty_main_row {
        padding: 15px 0px;
        /* border: 1px solid lightgrey; */
    }
    .btnGetChildSpecialty {
       width: 25px;
        padding: 0;
        font-size: 23px;
        margin-right: 5px;
        height: 25px;
        display: flex;
        display: -webkit-flex;
        align-items: center;
        -webkit-align-items: center;
        justify-content: center;
        -webkit-justify-content: center;
        color: #2D4379;
        font-weight: 400;
    }
    .child-specialty-main {
        margin-left: 30px;
    }
</style>
@endsection

@section('main-content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between items-center">
            <div class="page-title-box">
                <h4 class="page-title">@lang('cruds.specialty.title')</h4>
            </div>
            <div class="my-3">
                <a href="{{route('specialties.export')}}"  class="btn btn-outline-success">Export</a>
                <a href="javascript:void(0);"  class="btn btn-primary btnAddSpecialty" data-href="{{route('specialties.create')}}">Create</a>
            </div>
        </div>
        <!-- <div class="ccol-12 d-flex justify-content-between items-center">
            <form class="page-title-box my-3 importForm" id="importForm" enctype="multipart/form-data">
                @csrf
                <label for="csv_file">Upload CSV:</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv">
                <button class="btn btn-primary" type="submit">Import</button>
            </form>
        </div> -->
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-md-5 col-lg-4 col-xl-3 col-xxl-2">
                            <div class="dt-search mb-3">
                                <label for="specialtySearch">Search:</label>
                                <input type="search" class="form-control dt-input" id="specialtySearch" placeholder="" aria-controls="specialty-table">
                            </div>
                        </div>
                    </div>
                    <div class="specialty_main specialty_list">
                        @if($specialties->count() > 0)
                            @include('Specialty::partials.specialty-list', ['specialties' => $specialties, 'specialtyLevel' => $specialtyLevel])
                        @else
                            <div class="row specialty_main_row">
                                <div class="col-md-12 specialty__row">
                                    @lang('messages.no_record_found')
                                </div>
                            </div>
                        @endif
                    </div>
                    <div id="loading" style="display:none;text-align:center;">Loading...</div>
                </div>
            </div> 
        </div> 
    </div>
   


@endsection

@section('custom_js')

@include('Specialty::partials.script')

@endsection