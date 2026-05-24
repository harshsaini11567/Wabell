<div class="card-body">
     <div class="form-group">
        <label for="title_en">@lang('cruds.splash_screen.fields.title_en') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="title_en" name="title_en" value="{{ isset($splashScreen) ? $splashScreen->title_en : '' }}" required>
    </div>

    <div class="form-group">
        <label for="title_ar">@lang('cruds.splash_screen.fields.title_ar') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="title_ar" name="title_ar" value="{{ isset($splashScreen) ? $splashScreen->title_ar : '' }}" dir="rtl" required>
    </div>

    <div class="form-group">
        <label for="description_en">@lang('cruds.splash_screen.fields.description_en') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description_en" name="description_en" required rows="5">{{ isset($splashScreen) ? $splashScreen->description_en : '' }}</textarea>
    </div>

     <div class="form-group">
        <label for="description_ar">@lang('cruds.splash_screen.fields.description_ar') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description_ar" name="description_ar" dir="rtl" required rows="5">{{ isset($splashScreen) ? $splashScreen->description_ar : '' }}</textarea>
    </div>

    <div class="form-group col-md-12">
        <label for="splash_screen">@lang('cruds.splash_screen.fields.splash_screen_status')  <span class="text-danger">*</span></label>
        <select name="status" id="splash_screen_status" class="form-control select2" >
            <option value="">Select @lang('cruds.splash_screen.fields.splash_screen_status')</option>
            @foreach (config('constant.splash_screen_status') as $key => $status)
                <option value="{{$key}}" {{ isset($splashScreen) && $splashScreen->status == $key ? 'selected' : '' }}>{{$status}}</option>
            @endforeach
        </select> 
    </div>

    <div class="form-row">
        <div class="form-group col-md-12">
            <label class="form-label">@lang('cruds.splash_screen.fields.splash_image')</label>
            <input name="splash_image" type="file" class="dropify" id="splash_image" data-default-file=" {{ isset($splashScreen) &&  $splashScreen->splash_image_url ? $splashScreen->splash_image_url : '' }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg PNG JPG" accept="image/jpeg, image/png, image/jpg, image/PNG, image/JPG" />
        </div>
    </div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>