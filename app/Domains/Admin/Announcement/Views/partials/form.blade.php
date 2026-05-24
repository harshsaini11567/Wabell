<div class="card-body">
     <div class="form-group">
        <label for="title_en">@lang('cruds.announcement.fields.title_en') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="title_en" name="title_en" value="{{ isset($announcement) ? $announcement->title_en : '' }}" required>
    </div>

    <div class="form-group">
        <label for="title_ar">@lang('cruds.announcement.fields.title_ar') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="title_ar" name="title_ar" value="{{ isset($announcement) ? $announcement->title_ar : '' }}" required dir="rtl">
    </div>

    <div class="form-group">
        <label for="description_en">@lang('cruds.announcement.fields.description_en') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description_en" name="description_en" required>{{ isset($announcement) ? $announcement->description_en : '' }}</textarea>
    </div>

     <div class="form-group">
        <label for="description_ar">@lang('cruds.announcement.fields.description_ar') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description_ar" name="description_ar" required dir="rtl">{{ isset($announcement) ? $announcement->description_ar : '' }}</textarea>
    </div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>