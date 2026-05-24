<div class="modal fade edit_modal" id="AddFaq" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
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
                    $formUrl = route('faqs.store');
                    if(request()->routeIs('master-faqs.*')){
                       $formUrl = route('master-faqs.store');
                    } else if(request()->routeIs('web-faqs.*')){
                        $formUrl = route('web-faqs.store');
                    }
                @endphp
                <form id="addFaqForm" method="POST" data-store-url="{{ $formUrl }}">
                    @csrf
                    @include('Faq::partials.form',['groupedPermissions' => $groupedPermissions])
                </form>
            </div>
        </div>
    </div>
</div>