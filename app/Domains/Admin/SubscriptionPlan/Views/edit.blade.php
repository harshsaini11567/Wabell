<div class="modal fade edit_modal" id="editSubscriptionPlan" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">
                    {{ __('global.edit') }} @lang('cruds.menus.subscriptionPlan')
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSubscriptionPlanForm" data-href="{{ route('subscription-plans.update', $subscriptionPlan->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('SubscriptionPlan::partials.form')
                </form>
            </div>
        </div>
    </div>
</div>