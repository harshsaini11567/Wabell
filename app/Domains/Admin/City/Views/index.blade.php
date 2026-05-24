@extends('Layouts::app')
@section('title', __('cruds.city.title'))

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

    .btnUserWithoutCity,
    .btnUserWithoutNeighbour {
        position: relative;
        display: inline-block;
        padding-right: 35px;
    }

    .pac-container {
        z-index: 9999 !important; /* Ensure autocomplete suggestions appear above other elements */
    }
</style>
@endsection

@section('main-content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between items-center">
            <div class="page-title-box">
                <h4 class="page-title">@lang('cruds.city.title')</h4>
            </div>
            <div class="my-3 d-flex flex-column gap-2 d-md-block">
            <a href="{{ route('cities.user_without_location', ['type' => 'city']) }}" class="btn btn-primary btnUserWithoutCity">
                Cities
                <span class="pending_circle">
                    {{ $cityPendingCount ?? 0 }}
                </span>
            </a>

            <a href="{{ route('cities.user_without_location', ['type' => 'neighbor']) }}" class="btn btn-primary btnUserWithoutNeighbour">
                Neighborhood
                <span class="pending_circle">
                    {{ $neighborPendingCount ?? 0 }}
                </span>
            </a>

                <a href="javascript:void(0);"  class="btn btn-primary btnAddCity">Create</a>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="import_city_box">
                        <form class="page-title-box importCityForm" id="importCityForm" enctype="multipart/form-data">
                            @csrf
                            <label for="csv_file" class="form-label">Upload CSV:</label>
                            <div class="importCityForm_input">
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" />
                                <button class="btn btn-primary" type="submit">Import</button>
                            </div>
                        </form>
                    </div>
                </div>
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

@include('City::partials.script')


@endsection