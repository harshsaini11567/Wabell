<div class="modal fade show" id="ViewSubscriptionPlan" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.subscription_plan.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.name_en')</th>
                                    <td> {{ $subscriptionPlan->name_en ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.name_ar')</th>
                                    <td dir="rtl"> {{ $subscriptionPlan->name_ar ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.monthly_price')</th>
                                    <td> {{ $subscriptionPlan->monthly_price ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.yearly_price')</th>
                                    <td> {{ $subscriptionPlan->yearly_price ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.status')</th>
                                    <td> {{ isset($subscriptionPlan->is_active) ? config('constant.plan_status')[$subscriptionPlan->is_active] : '-' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.features_en')</th>
                                    <td> {!! $subscriptionPlan->features_en ?? 'N/A' !!} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.features_ar')</th>
                                    <td dir="rtl"> {!! $subscriptionPlan->features_ar ?? 'N/A' !!} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.plan_image')</th>
                                    <td>
                                        @if(!empty($subscriptionPlan->plan_image_url))
                                            <a href="{{ $subscriptionPlan->plan_image_url }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ $subscriptionPlan->plan_image_url }}" alt="Plan Image" width="100px">
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.verified_icon')</th>
                                    <td>
                                        @if(!empty($subscriptionPlan->verified_icon_url))
                                            <a href="{{ $subscriptionPlan->verified_icon_url }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ $subscriptionPlan->verified_icon_url }}" alt="Verified Image" width="100px">
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.subscription_plan.fields.created_at')</th>
                                    <td> {{ $subscriptionPlan->created_at->format(config('constant.date_format.date_time')) }} </td>
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
