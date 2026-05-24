<div class="modal fade edit_modal" id="AddSplashScreen" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">
                        {{ __('global.create') }} @lang('cruds.menus.splashScreen')
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSplashScreenForm" method="POST" data-store-url="{{ route('splash-screens.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('SplashScreen::partials.form',['groupedPermissions' => $groupedPermissions])
                </form>
            </div>
        </div>
    </div>
</div>