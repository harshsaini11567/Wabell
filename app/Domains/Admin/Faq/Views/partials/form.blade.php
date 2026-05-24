<div class="card-body">
     <div class="form-group">
        <label for="question_en">@lang('cruds.faq.fields.question_en') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="question_en" name="question_en" value="{{ isset($faq) ? $faq->question_en : '' }}" required>
    </div>

    <div class="form-group">
        <label for="question_ar">@lang('cruds.faq.fields.question_ar') <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="question_ar" name="question_ar" value="{{ isset($faq) ? $faq->question_ar : '' }}" dir="rtl" required>
    </div>

    <div class="form-group">
        <label for="answer_en">@lang('cruds.faq.fields.answer_en') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="answer_en" name="answer_en" required rows="5">{{ isset($faq) ? $faq->answer_en : '' }}</textarea>
    </div>

     <div class="form-group">
        <label for="answer_ar">@lang('cruds.faq.fields.answer_ar') <span class="text-danger">*</span></label>
        <textarea class="form-control" id="answer_ar" name="answer_ar" dir="rtl" required rows="5">{{ isset($faq) ? $faq->answer_ar : '' }}</textarea>
    </div>
</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>