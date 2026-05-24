<div class="modal fade edit_modal" id="ChangePassword" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.change_password')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ChangePasswordForm" data-href="{{route('admins.change-password', $id)}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="password" class="form-label">@lang('global.login_password')</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" tabindex="2" required autocomplete="new-password">
                                    <div class="input-group-text toggle-password show-password" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">@lang('cruds.admin.fields.confirm_password')</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Enter your password" tabindex="2" required autocomplete="new-password">
                                    <div class="input-group-text toggle-password show-password" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>