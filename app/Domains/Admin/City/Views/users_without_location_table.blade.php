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

    .pac-container {
        z-index: 9999 !important; /* Ensure autocomplete suggestions appear above other elements */
    }
</style>
@endsection

@section('main-content')
    <div class="row">
         @php
            $type = request('type');
            $title = match ($type) {
                'city' => __('cruds.city.title_singular'),
                'neighbor' => __('cruds.neighborhood.title_singular'),
                default => __('global.users_without_location'),
            };
        @endphp
        <div class="col-12">
            <div class="my-1"><a class="text-decoration-underline" href="{{ route('cities.index') }}" title="City">Cities/ Neighborhoods</a> / Other {{ $title }} Users</div>
        </div>
        <div class="col-12 d-flex justify-content-between items-center">
            <div class="page-title-box my-3">
               

                <h4 class="page-title">Other {{ $title }} Users</h4>
            </div>
        </div>
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

    <div class="modal fade" id="ViewCustomer" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('global.show') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- AJAX content will be injected here -->
                <div class="text-center">
                    <span class="spinner-border spinner-border-sm"></span> Loading...
                </div>
            </div>
        </div>
    </div>
</div>

   


@endsection

@section('custom_js')

@include('City::partials.script')


@endsection