<div class="modal fade edit_modal" id="AddSpecialty" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.create') @lang('cruds.specialty.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $createRoute = route('specialties.store');
                    if(isset($id) && !empty($id)){
                        $createRoute = route('specialties.store', $id);
                    }
                @endphp
                <form id="AddSpecialtyForm" data-href="{{$createRoute}}" enctype="multipart/form-data">
                    @csrf
                    @include('Specialty::partials.form')
                </form>
            </div>
        </div>
    </div>
</div>