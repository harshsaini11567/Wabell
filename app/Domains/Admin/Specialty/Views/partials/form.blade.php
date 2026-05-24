<div class="card-body">
    <div class="form-group">
        <label for="name_en">@lang('cruds.specialty.fields.name_en') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ isset($specialty) ? $specialty->name_en : '' }}" required>
    </div>
    <div class="form-group">
        <label for="name_ar">@lang('cruds.specialty.fields.name_ar') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ isset($specialty) ? $specialty->name_ar : '' }}" dir="rtl" required>
    </div>

    <div class="mb-3">
        <label class="form-label" for="specialty_icon">Specialty Icon <small>(Dimension: 150*150)</small></label>
        <input type="file" id="image-input" name="specialty_icon" class="form-control fileInputBoth" accept="image/*">
        @php
            $iconUrl = isset($specialty) && $specialty->specialty_icon_url
                ? $specialty->specialty_icon_url
                : asset(config('constant.default.no_image'));
        @endphp

        <div class="img-prevarea mt-3 {{ isset($specialty) && $specialty->specialty_icon_url ? 'active' : '' }}">
            <img src="{{ $iconUrl }}" width="100px" height="100px">

            @if(isset($specialty) && $specialty->specialty_icon_url)
                <div class="remove-specialty-icon-main">
                    <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm" title="Remove" id="RemoveSpecialtyIconBtn" data-specialty_id="{{ isset($specialty) ? $specialty->uuid : '' }}">
                        <i class="ri-delete-bin-line"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
    

<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>