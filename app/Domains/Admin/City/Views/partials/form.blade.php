<div class="card-body">
    <div class="form-group">
        <label for="name_en">@lang('cruds.city.fields.name_en') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ isset($city) ? $city->name_en : '' }}" required>
    </div>
    <div class="form-group">
        <label for="name_ar">@lang('cruds.city.fields.name_ar') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ isset($city) ? $city->name_ar : '' }}" required dir="rtl">
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="lat">@lang('cruds.city.fields.lat') <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="lat" name="lat" placeholder="Eg: 12.3456789" value="{{ isset($city) ? $city->lat : '' }}" required>
        </div>

        <div class="col-md-6 form-group">
            <label for="lng">@lang('cruds.city.fields.lng') <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="lng" name="lng" placeholder="Eg: 12.3456789" value="{{ isset($city) ? $city->lng : '' }}" required>
        </div>
    </div>

    <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>