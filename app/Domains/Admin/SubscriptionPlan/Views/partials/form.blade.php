<div class="card-body">
    <div class="form-group">
        <label for="name">@lang('cruds.subscription_plan.fields.name_en') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ isset($subscriptionPlan) ? $subscriptionPlan->name_en : '' }}" required>
    </div>

    <div class="form-group">
        <label for="name">@lang('cruds.subscription_plan.fields.name_ar') <span class="text-danger">*</span></label>
        <input dir="rtl" type="text" class="form-control" id="name_ar" name="name_ar" value="{{ isset($subscriptionPlan) ? $subscriptionPlan->name_ar : '' }}" required>
    </div>

    <div class="form-group">
        <label for="monthly_price">@lang('cruds.subscription_plan.fields.monthly_price') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="monthly_price" name="monthly_price" value="{{ isset($subscriptionPlan) ? $subscriptionPlan->monthly_price : '' }}" required>
    </div>

    <div class="form-group">
        <label for="yearly_price">@lang('cruds.subscription_plan.fields.yearly_price') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="yearly_price" name="yearly_price" value="{{ isset($subscriptionPlan) ? $subscriptionPlan->yearly_price : '' }}" required>
    </div>

    <!-- <div class="form-group">
        <label for="is_active">@lang('cruds.subscription_plan.fields.status')  <span class="text-danger">*</span></label>
        <select name="is_active" id="is_active" class="form-control select2" >
            <option value="">Select @lang('cruds.subscription_plan.fields.status')</option>
            @foreach (config('constant.plan_status') as $key => $status)
                <option value="{{$key}}" {{ isset($subscriptionPlan) && $subscriptionPlan->is_active == $key ? 'selected' : '' }}>{{$status}}</option>
            @endforeach
        </select> 
    </div> -->

    <div class="form-row">
        <div class="form-group col-md-12">
            <label class="form-label">@lang('cruds.subscription_plan.fields.plan_image')</label>
            <input name="plan_image" type="file" class="dropify" id="plan_image" data-default-file=" {{ isset($subscriptionPlan) &&  $subscriptionPlan->plan_image_url ? $subscriptionPlan->plan_image_url : '' }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg PNG JPG" accept="image/jpeg, image/png, image/jpg, image/PNG, image/JPG" />
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-12">
            <label class="form-label">@lang('cruds.subscription_plan.fields.verified_icon')</label>
            <input name="verified_icon" type="file" class="dropify" id="verified_icon" data-default-file=" {{ isset($subscriptionPlan) &&  $subscriptionPlan->verified_icon_url ? $subscriptionPlan->verified_icon_url : '' }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg PNG JPG" accept="image/jpeg, image/png, image/jpg, image/PNG, image/JPG" />
        </div>
    </div>

    <div class="form-group">
        <label for="features_en">@lang('cruds.subscription_plan.fields.features_en') <span class="text-danger">*</span></label>
        <textarea class="tinymce-editor" id="features_en" name="features_en" data-dir="ltr">{{ isset($subscriptionPlan) ? $subscriptionPlan->features_en : '' }}</textarea>
    </div>

    <div class="form-group">
        <label for="features_ar">@lang('cruds.subscription_plan.fields.features_ar') <span class="text-danger">*</span></label>
        <textarea class="tinymce-editor" id="features_ar" name="features_ar" data-dir="rtl">{{ isset($subscriptionPlan) ? $subscriptionPlan->features_ar : '' }}</textarea>
    </div>
</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>