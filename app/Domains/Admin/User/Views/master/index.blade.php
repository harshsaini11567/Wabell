@extends('Layouts::app')
@section('title', __('cruds.master.title'))

@section('custom_css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.0/css/responsive.dataTables.min.css">

<link href="{{ asset('admin-assets/vendor/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/vendor/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('main-content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between items-center">
            <div class="page-title-box my-3">
                <h4 class="page-title">@lang('cruds.master.title')</h4>
            </div>
            <div class="user-dropdown my-3 checkbox switch d-flex align-items-center gap-1">
                <span>@lang('global.user_status')</span>
                <select class="form-select" id="added_required_fields_users" name="added_required_fields_users" style="width: 200px;">
                    <option value="">@lang('global.all')</option>
                    <option value="verified">Verified Users</option>
                    <option value="unverified">Unverified Users</option>
                </select>
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

    @include('User::master.partials.script')

@endsection