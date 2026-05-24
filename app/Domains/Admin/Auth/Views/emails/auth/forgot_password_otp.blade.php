@extends('Layouts::emails.layouts.admin')
@section('styles')
@endsection

@section('email-content')
    <tr>
        <td>
            <p class="mail-title" style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">
                {!! trans('emails.forgot_password_otp_mail_user.body.line1', ['user_name' => $user->name], $language) !!}
            </p>
            <div class="mail-desc">
                <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.forgot_password_otp_mail_user.body.line2', [], $language) !!}</p>
                <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.forgot_password_otp_mail_user.body.line3', ['token' => $token], $language ) !!}</p>

                <p style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.forgot_password_otp_mail_user.body.line4', ['expiretime' => $expiretime], $language ) !!}</p>

                <div class="regards" style="line-height: 10.5px; font-weight: 600; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

            </div>
        </td>
    </tr>
@endsection
