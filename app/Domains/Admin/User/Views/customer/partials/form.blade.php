<div class="card-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label for="name">@lang('cruds.customer.fields.name') <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control {{ empty($customer->name ?? '') ? 'border-orange text-orange' : '' }}" 
                   id="name" 
                   name="name" 
                   value="{{ $customer->name ?? '' }}" 
                   required>
        </div>
        <div class="form-group col-md-6">
            <label for="email">@lang('cruds.customer.fields.email')</label>
            <input type="text" 
                   class="form-control {{ empty($customer->email ?? '') ? 'border-orange text-orange' : '' }}" 
                   id="email" 
                   name="email" 
                   value="{{ $customer->email ?? '' }}" 
                   required 
                   autocomplete="username" 
                   readonly 
                   disabled>
        </div>
        <div class="form-group col-md-6">
            <label for="phone">@lang('cruds.customer.fields.phone')</label>
            <input type="text" 
                   class="form-control {{ empty($customer->phone ?? '') ? 'border-orange text-orange' : '' }}" 
                   id="phone" 
                   name="phone" 
                   value="{{ isset($customer) ? $customer->country_code  . ' ' . $customer->phone : '' }}" 
                   required 
                   autocomplete="username" 
                   readonly 
                   disabled>
        </div>
        <div class="form-group col-md-6">
            <label for="user_status">@lang('cruds.customer.fields.user_status')  <span class="text-danger">*</span></label>
            <select name="user_status" 
                    id="user_status" 
                    class="form-control select2 {{ empty($customer->user_status ?? '') ? 'border-orange text-orange' : '' }}">
                <option value="">Select @lang('cruds.customer.fields.user_status')</option>
                @foreach (config('constant.user_status') as $key => $status)
                    <option value="{{$key}}" {{ isset($customer) && $customer->user_status == $key ? 'selected' : '' }}>{{$status}}</option>
                @endforeach
            </select> 
        </div>
        <div class="form-group col-md-12">
            <label class="form-label">@lang('cruds.customer.fields.profile_image')</label>
            <input name="profile_image" 
                   type="file" 
                   class="dropify {{ empty($customer->profile_image_url ?? '') ? 'border-orange' : '' }}" 
                   id="profile_image" 
                   data-default-file="{{ $customer->profile_image_url ?? '' }}"  
                   data-show-loader="true" 
                   data-errors-position="outside" 
                   data-allowed-file-extensions="jpeg png jpg PNG JPG" 
                   accept="image/jpeg, image/png, image/jpg, image/PNG, image/JPG" />
        </div>
    </div>
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>
