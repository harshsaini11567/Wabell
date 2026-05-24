@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h4 style="font-weight:600; margin-top: 0; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.user_register_mail_super_admin.body.line1', ['name' => $name], $language) !!}</h4>
    <p style="font-weight:400; line-height: 25.5px; margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.user_register_mail_super_admin.body.line2', [], $language) !!}</p>
    
    <ul style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">
        <li>{!! trans('emails.user_register_mail_super_admin.body.line3', ['username' => $username], $language) !!}</li>
        <li>{!! trans('emails.user_register_mail_super_admin.body.line4', ['userEmail' => $userEmail], $language) !!}</li>
        <li>{!! trans('emails.user_register_mail_super_admin.body.line5', ['role' => $role], $language) !!}</li>
        <li>{!! trans('emails.user_register_mail_super_admin.body.line6', ['phone_number' => $phoneNumber], $language) !!}</li>
    </ul>

    <p style="font-weight:400; line-height: 25.5px;  margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.user_register_mail_super_admin.body.line7', [], $language) !!}</p>

    <div class="regards" style="font-weight:600; line-height: 10.5px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
