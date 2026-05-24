@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h4 style="font-weight: 600;margin-top: 0; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.speciality_request_mail_super_admin.body.line1', ['super_admin_name' => $name], $language) !!}</h4>
    <p style="line-height: 25.5px; font-weight: 400;margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.speciality_request_mail_super_admin.body.line2', [], $language) !!}</p>

    <p style="line-height: 25.5px; font-weight: 400;margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.speciality_request_mail_super_admin.body.line3', [], $language) !!}</p>

    <a href="{{ $specialty_request_url }}" style="color:#fff; text-transform: uppercase; border-radius: 5px; background-color: #006AF2;box-shadow:8px 6px 15px 0px rgba(0, 97, 222, 0.25); padding: 21px 28px; display: inline-block; text-decoration: none;margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.speciality_request_mail_super_admin.body.button', [], $language) !!}</a>

    <p style="line-height: 25.5px; font-weight: 400;margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}"> {!! trans('emails.speciality_request_mail_super_admin.body.line4', ['specialty_request_url' => $specialty_request_url], $language) !!}</p>

    <div class="regards" style="line-height: 10.5px; font-weight: 600; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
