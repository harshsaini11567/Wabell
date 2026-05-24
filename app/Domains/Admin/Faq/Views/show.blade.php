<div class="modal fade show" id="ViewFaq" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.faq.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.faq.fields.question_en')</th>
                                    <td> {{ $faq->question_en ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.faq.fields.question_ar')</th>
                                    <td> {{ $faq->question_ar ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.faq.fields.answer_en')</th>
                                    <td> {{ $faq->answer_en ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.faq.fields.answer_ar')</th>
                                    <td> {{ $faq->answer_ar ?? 'N/A' }} </td>
                                </tr>
                                {{-- <tr>
                                    <th> @lang('cruds.faq.fields.faq_status')</th>
                                    <td> {{ config('constant.status.' . $faq->faq_status, 'N/A') }} </td>
                                </tr> --}}
                                <tr>
                                    <th> @lang('cruds.faq.fields.created_at')</th>
                                    <td> {{ $faq->created_at->format(config('constant.date_format.date_time')) }} </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
