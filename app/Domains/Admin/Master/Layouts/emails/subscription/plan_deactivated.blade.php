@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h2 style="font-weight: 600; font-size: 18px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.subscription_plan_deactivated_master.body.line1',['user_name' => $user->name], $language) !!},</h2>

    <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.subscription_plan_deactivated_master.body.line2', ['plan_name' => $plan->name_en], $language) !!}</p>

    <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.subscription_plan_deactivated_master.body.line3', ['end_date' => \Carbon\Carbon::parse($endDate)->format('d M Y') ], $language)!!}</p>

    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; line-height: 10.5px; font-weight: 700; font-size: 18px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
