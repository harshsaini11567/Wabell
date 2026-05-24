@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h4 style="font-weight:600; margin-top: 0; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.user_selected_other_location_mail.body.line1', [], $language) !!}</h4>
    <p style="font-weight:400; line-height: 25.5px; margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.user_selected_other_location_mail.body.line2', [], $language) !!}</p>
    
    <ul style="text-align: {{ $language == 'ar' ? 'right' : 'left' }}">
        <li>{!! trans('emails.user_selected_other_location_mail.body.line3', ['name' => $user->name], $language) !!}</li>
        <li>{!! trans('emails.user_selected_other_location_mail.body.line4', ['email' => $user->email], $language) !!}</li>
        <li>{!! trans('emails.user_selected_other_location_mail.body.line5', ['phone' => $user->phone], $language) !!}</li>
        <li>{!! trans('emails.user_selected_other_location_mail.body.line6', ['city' => $city], $language) !!}</li>
        <li>{!! trans('emails.user_selected_other_location_mail.body.line7', ['neighborhood' => $neighborhood], $language) !!}</li>
        <li>{!! trans('emails.user_selected_other_location_mail.body.line8', ['role' => $role], $language) !!}</li>
    </ul>

    <div class="regards" style="font-weight:600; line-height: 10.5px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
