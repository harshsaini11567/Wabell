<div class="modal fade edit_modal" id="editSpecialty" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.edit') @lang('cruds.specialty.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSpecialtyForm" data-href="{{route('specialties.update', $specialty->uuid)}}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('Specialty::partials.form')
                </form>
            </div>
        </div>
    </div>
</div>