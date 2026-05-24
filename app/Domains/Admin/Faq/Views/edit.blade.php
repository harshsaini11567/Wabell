<div class="modal fade edit_modal" id="editFaq" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">
                    @if (Request::is('faq*'))
                        {{ __('global.create') }} @lang('cruds.menus.faq')
                    @elseif (Request::is('master-faq*'))
                        {{ __('global.create') }} @lang('cruds.menus.masterFaq')
                    @elseif (Request::is('web-faq*'))
                        {{ __('global.create') }} @lang('cruds.menus.web_faq')
                    @endif
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $formUrl = route('faqs.update', $faq->id);
                    if(request()->routeIs('master-faqs.*')){
                       $formUrl = route('master-faqs.update', $faq->id);
                    } else if(request()->routeIs('web-faqs.*')){
                        $formUrl = route('web-faqs.update', $faq->id);
                    }
                @endphp
                <form id="editFaqForm" data-href="{{ $formUrl }}">
                    @csrf
                    @method('PUT')
                    @include('Faq::partials.form')
                </form>
            </div>
        </div>
    </div>
</div>