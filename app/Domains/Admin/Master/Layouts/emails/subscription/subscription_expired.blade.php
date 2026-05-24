@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h2 style="font-weight: 600; font-size: 18px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.master_subscription_expired_mail.body.line1', ['name' => $user->name], $language) }}</h2>

    <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.master_subscription_expired_mail.body.line2',[], $language) }}</p>

    <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.master_subscription_expired_mail.body.line3',  [], $language ) }}</p>
    <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.master_subscription_expired_mail.body.line4',  [], $language ) }}</p>
    <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.master_subscription_expired_mail.body.line5',  [], $language ) }}</p>

    
    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; line-height: 10.5px; font-weight: 700; font-size: 18px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
