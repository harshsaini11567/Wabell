<div class="modal fade edit_modal" id="editSplashScreen" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">
                    {{ __('global.edit') }} @lang('cruds.menus.splashScreen')
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSplashScreenForm" data-href="{{ route('splash-screens.update', $splashScreen->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('SplashScreen::partials.form')
                </form>
            </div>
        </div>
    </div>
</div>