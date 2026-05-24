@extends('Layouts::app')
@section('title', __('cruds.neighborhood.title'))

@section('custom_css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.0/css/responsive.dataTables.min.css">

<link href="{{ asset('admin-assets/vendor/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css" />

<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 8px;
    }
    /* Style for the input inside the map */
    #map-search {
        box-sizing: border-box;
        border: 1px solid transparent;
        width: 240px;
        height: 40px;
        margin-top: 10px;
        padding: 0 12px;
        border-radius: 4px;
        font-size: 14px;
        outline: none;
        text-overflow: ellipsis;
    }

    .pac-container {
        z-index: 9999 !important; /* Ensure autocomplete suggestions appear above other elements */
    }
</style>
@endsection

@section('main-content')
    <div class="row">
        <div class="col-12">
            <div class=""><a class="text-decoration-underline" href="{{ route('cities.index') }}" title="City">Cities</a> / {{ $city->name_en }} / Neighborhoods</div>
        </div>
        <div class="col-12 d-flex justify-content-between items-center">
            <div class="page-title-box">
                <h4 class="page-title">@lang('cruds.neighborhood.title')</h4>
            </div>
            <div class="my-2">
                <a href="javascript:void(0);"  data-href="{{ route('cities.neighborhoods.create', $city->uuid) }}"  class="btn btn-primary btnAddNeighborhood">Create</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                       
                        {{$dataTable->table(['class' => 'table mb-0', 'style' => 'width:100%;'])}}
                           
                    </div> 
                </div>
            </div> 
        </div> 
    </div>
   


@endsection

@section('custom_js')

@include('City::Neighborhood.partials.script')

@endsection