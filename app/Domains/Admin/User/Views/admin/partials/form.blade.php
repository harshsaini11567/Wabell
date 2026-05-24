<div class="card-body">
    <div class="row">
        <div class="form-group">
            <label for="name">@lang('cruds.admin.fields.name') <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="{{ isset($admin) ? $admin->name : '' }}" required>
        </div>

        <div class="form-group col-md-6">
            <label for="email">@lang('cruds.admin.fields.email')<span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="email" name="email" value="{{ isset($admin) ? $admin->email : '' }}" required autocomplete="username">
        </div>

        <div class="form-group col-md-6">
            <label for="phone">@lang('cruds.admin.fields.phone') <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ isset($admin) ? $admin->phone : '' }}" required>
        </div>

        @if(!isset($admin))
            <div class="form-group col-md-6">
                <label for="password" class="form-label">@lang('global.login_password')</label>
                <div class="input-group input-group-merge">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" tabindex="2" required autocomplete="new-password">
                    <div class="input-group-text toggle-password show-password" data-password="false">
                        <span class="password-eye"></span>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="password_confirmation" class="form-label">@lang('cruds.admin.fields.confirm_password')</label>
                <div class="input-group input-group-merge">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Enter your password" tabindex="2" required autocomplete="new-password">
                    <div class="input-group-text toggle-password show-password" data-password="false">
                        <span class="password-eye"></span>
                    </div>
                </div>
            </div>
        @endif

        <div class="form-group col-md-6">
            <label for="roles" class="form-label">@lang('cruds.admin.fields.roles') <span class="text-danger">*</span></label>
            <div class="row" id="roles">
                @foreach($roles as $role)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="role_{{ $role->uuid }}"
                                name="roles[]"
                                value="{{ $role->uuid }}"
                                {{ isset($admin) && $admin->roles->contains($role->id) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="perm_{{ $role->uuid }}">{{ $role->name_en }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group col-md-6">
            <label for="user_status">@lang('cruds.admin.fields.user_status')  <span class="text-danger">*</span></label>
            <select name="user_status" id="user_status" class="form-control select2" >
                <option value="">Select @lang('cruds.admin.fields.user_status')</option>
                @foreach (config('constant.user_status') as $key => $status)
                    <option value="{{$key}}" {{ isset($admin) && $admin->user_status == $key ? 'selected' : '' }}>{{$status}}</option>
                @endforeach
            </select> 
        </div>

        <div class="form-group col-md-12">
            <label class="form-label">@lang('cruds.admin.fields.profile_image')</label>
            <input name="profile_image" type="file" class="dropify" id="profile_image" data-default-file=" {{ isset($admin) &&  $admin->profile_image_url ? $admin->profile_image_url : '' }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg PNG JPG" accept="image/jpeg, image/png, image/jpg, image/PNG, image/JPG" />
        </div>
    </div>
</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>