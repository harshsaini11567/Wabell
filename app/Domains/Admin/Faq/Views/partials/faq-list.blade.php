@foreach ($faqs as $key => $faq)    
    <div class="accordion-item faq_inner">
        <div class="row align-items-center mx-0 faq_question_header">
            <div class="col-md-9 col-lg-10">
                <div class="header_question">
                    <h6>@lang('cruds.faq.fields.question_en'):</h6>
                    <p>{{ $faq->question_en }}</p>
                </div>
                <div class="header_question">
                    <h6>@lang('cruds.faq.fields.question_ar'):</h6>
                    <p dir="rtl">{{ $faq->question_ar }}</p>
                </div>
            </div>
            <div class="col-md-3 col-lg-2 text-end">
                @php
                    $editUrl = route('faqs.edit', $faq->id);
                    $deleteUrl = route('faqs.destroy', $faq->id);
                    if(request()->routeIs('master-faqs.*')){
                       $editUrl = route('master-faqs.edit', $faq->id);
                        $deleteUrl = route('master-faqs.destroy', $faq->id);
                    } else if(request()->routeIs('web-faqs.*')){
                        $editUrl = route('web-faqs.edit', $faq->id);
                        $deleteUrl = route('web-faqs.destroy', $faq->id);
                    }
                @endphp
                <div class="faq_btns">
                    <a href="javascript:void(0)" class="btn btn-outline-dark btn-sm btnEditFaq"  data-href="{{ $editUrl }}" data-step="0"><i class="ri-pencil-line"></i></a>
                    <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm deleteFaqBtn"  data-href="{{ $deleteUrl }}" data-step="0"><i class="ri-delete-bin-line"></i></a>
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq_collapse_{{$faq->id}}" aria-expanded="true" aria-controls="faq_collapse_{{$faq->id}}"></button>
                </div>
            </div>
        </div>
        <div id="faq_collapse_{{$faq->id}}" class="accordion-collapse collapse {{-- {{ $key == 0 ? 'show' : '' }} --}}" aria-labelledby="faq_header_{{$faq->id}}"
            data-bs-parent="#accordion_faq">
            <div class="accordion-body">
                <div class="body_answer">
                    <h6>@lang('cruds.faq.fields.answer_en'):</h6>
                    <p>{{ $faq->answer_en }}</p>
                </div>
                <div class="body_answer">
                    <h6>@lang('cruds.faq.fields.answer_ar'):</h6>
                    <p dir="rtl">{{ $faq->answer_ar }}</p>
                </div>
            </div>
        </div>
    </div>
@endforeach
