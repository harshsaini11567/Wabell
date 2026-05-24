@extends('emails.layouts.admin')

@section('email-content')

    <p>Hi {{getSetting('support_name') ? getSetting('support_name') : config('constant.support.name')}},</p>

    <p>This email notifies you of a new contact us submission received on {{$contactUsDate}} at {{$contactUsTime}}.</p>

    <b>User Information:</b>

    <ul>
        <li>Name: @if($userName) {{$userName}} @else N/A @endif</li>
        <li>Email: @if($userEmail) {{$userEmail}} @else N/A @endif</li>
    </ul>

    <p><b>Subject:</b></p> 
    
    <p>{{$contactUsSubject}}</p>

    <p><b>Message:</b></p>

    <p>{{$message}}</p>

    <p><b>How to Respond:</b></p>

    <p>You can easily reply directly to this email to reach the user.</p>

    <p>Thank you,</p>

    <p>The {{ getSetting('site_title') ? getSetting('site_title') : config('app.name') }} Team</p>

@endsection
