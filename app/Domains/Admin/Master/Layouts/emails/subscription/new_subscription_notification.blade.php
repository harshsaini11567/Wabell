@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h4 style="font-weight: 600; font-size: 18px; margin-top: 0; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.subscription_activation_mail_super_admin.body.line1', ['super_admin_name' => $superadminName], $language) !!}</h4>
    <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.subscription_activation_mail_super_admin.body.line2', [], $language) !!}</p>

    <ul style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line3', ['user_name' => $user->name], $language) !!}</li>
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line4', ['user_email' => $user->email], $language) !!}</li>
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line5', ['phone_number' => $user->phone], $language) !!}</li>
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line6', ['plan_name' => $plan->name_en], $language) !!}</li>
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line7', ['billing_cycle' => ucfirst($billingCycle)], $language) !!}</li>
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line8', ['price' => number_format($price, 2)], $language) !!}</li>
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line9', ['start_date' => $startDate->format('d M Y')], $language) !!}</li>
        <li>{!! trans('emails.subscription_activation_mail_super_admin.body.line10', ['end_date' => $endDate->format('d M Y')], $language) !!}</li>
    </ul>


    <div class="regards" style="line-height: 10.5px; font-weight: 600; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
