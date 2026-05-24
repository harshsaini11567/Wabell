<div class="modal fade show" id="ViewTransactionPlan" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.transaction.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.transaction.fields.user_id')</th>
                                    <td> {{ ucwords($transaction->user->name) ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.transaction.fields.plan_id')</th>
                                    <td> 
                                        @php  
                                            $plan = optional($transaction->subscription->plan);  
                                        @endphp
                                            {{ $plan ? $plan->name_en : 'Basic' }}
                                     </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.transaction.fields.amount')</th>
                                    <td> {{ $transaction->amount ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.transaction.fields.billing_cycle')</th>
                                    <td> {{ config('constant.plan_billing_cycle.' . ($transaction->subscription->billing_cycle ?? '')) ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.transaction.fields.subscription_status')</th>
                                    <td> {{ config('constant.subscription_status.' . ($transaction->subscription->status ?? '')) ?? 'N/A' }} </td>
                                </tr>

                                <tr>
                                    <th style="width:150px;"> @lang('cruds.transaction.fields.payment_status')</th>
                                    <td> {{ config('constant.payment_status.' . ($transaction->payment_status ?? '')) ?? 'N/A' }} </td>
                                </tr>
                                
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.transaction.fields.created_at')</th>
                                    <td> {{ $transaction->created_at->format(config('constant.date_format.date_time')) }} </td>
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
