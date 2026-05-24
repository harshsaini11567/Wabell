@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h4 style="font-weight: 600; margin-top: 0; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.reset_password_mail_user.body.line1', ['user_name' => $user->name], $language) }}</h4>
    <p style="line-height: 25.5px; font-weight: 400; margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.reset_password_mail_user.body.line2', [], $language) !!}</p>
    <p style="line-height: 25.5px; font-weight: 400; margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.reset_password_mail_user.body.line3', ['email' => $user->email], $language) !!}</p>

    <a href="{{ $reset_password_url }}" style="color:#fff; text-transform: uppercase; line-height: 13px; border-radius: 5px; background-color: #006AF2;box-shadow:8px 6px 15px 0px rgba(0, 97, 222, 0.25); padding: 21px 28px; display: inline-block; text-decoration: none;margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.reset_password_mail_user.body.button', [], $language) !!}</a>

    <p style="line-height: 25.5px; font-weight: 400; margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.reset_password_mail_user.body.line4', ['reset_password_url' => $reset_password_url], $language) !!}</p>

    <div class="regards" style="line-height: 10.5px; font-weight: 600; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
