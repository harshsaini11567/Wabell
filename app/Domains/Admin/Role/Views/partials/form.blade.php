<div class="card-body">
     <div class="form-group">
        <label for="name_en">@lang('cruds.role.fields.name_en') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ isset($role) ? $role->name_en : '' }}" required>
    </div>

    <div class="form-group">
        <label for="name_ar">@lang('cruds.role.fields.name_ar') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ isset($role) ? $role->name_ar : '' }}" required dir="rtl">
    </div>

    <div class="form-group">
        <label for="description_en">@lang('cruds.role.fields.description_en') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description_en" name="description_en" required>{{ isset($role) ? $role->description_en : '' }}</textarea>
    </div>

     <div class="form-group">
        <label for="description_ar">@lang('cruds.role.fields.description_ar') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description_ar" name="description_ar" required dir="rtl">{{ isset($role) ? $role->description_ar : '' }}</textarea>
    </div>

    <div class="form-group">
        <label for="">@lang('cruds.role.fields.role_status')</label>
        <select name="role_status" id="role_status" class="form-control select2">
            <option value="">Select @lang('cruds.role.fields.role_status')</option>
            @foreach (config('constant.status') as $roleStatuskey => $roleStatus)
                <option value="{{$roleStatuskey}}" {{ isset($role) && $role->role_status == $roleStatuskey ? 'selected' : '' }}>{{$roleStatus}}</option>
            @endforeach
        </select>    
    </div>

    <div class="form-group in_label_style">
        <label for="permissions">@lang('cruds.role.fields.permission') <span class="text-danger">*</span></label>
        <div>
        @foreach($groupedPermissions as $module => $permissions)
            <div class="mb-3 border p-2">
                <!-- <h5 class="text-capitalize">{{ $module }}</h5> -->
                 {{ $module === 'faq' ? 'FAQ' : ucwords(str_replace('_', ' ', $module)) }}
                <div class="row">
                    @foreach($permissions as $permission)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="perm_{{ $permission->id }}"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    {{ isset($role) && $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                    {{ str_replace( '_', ' ', ucfirst(str_replace($module . '_', '', $permission->title))) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        </div>
    </div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>