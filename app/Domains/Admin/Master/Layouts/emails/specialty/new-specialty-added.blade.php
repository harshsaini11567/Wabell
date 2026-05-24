@extends('Layouts::emails.layouts.admin')

@section('email-content')

    <h4 style="font-weight: 600;margin-top: 0; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.speciality_added_by_master_mail_student.body.line1', ['user_name' => $name], $language) !!}</h4>
    <p style="line-height: 25.5px; font-weight: 400; margin-bottom: 27px; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{!! trans('emails.speciality_added_by_master_mail_student.body.line2', ['specialty_name' => $specialty_name], $language) !!}</p>

    <div class="regards" style="line-height: 10.5px; font-weight: 600; text-align: {{ $language == 'ar' ? 'right' : 'left' }}">{{ trans('emails.regards', [], $language) }},<br><br><br> {{ trans('emails.wabell', [], $language) }}</div>

@endsection
