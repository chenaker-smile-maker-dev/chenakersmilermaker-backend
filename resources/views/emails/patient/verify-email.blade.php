@extends('emails.patient.layouts.base')

@section('title', 'Verify Your Email')

@section('content')
    <h2>Hello, {{ $patient->first_name }}!</h2>

    <p>Thank you for registering with <strong>Chenaker Smile Maker</strong>. Please verify your email address by clicking the button below.</p>

    <p style="text-align: center; margin: 32px 0;">
        <a href="{{ $verificationUrl }}" class="btn">
            ✉️ Verify Email Address
        </a>
    </p>

    <p>If you did not create an account, no further action is required.</p>

    <p style="font-size: 12px; color: #888888;">
        If the button does not work, copy and paste this URL into your browser:<br>
        <a href="{{ $verificationUrl }}" style="color: #25703e; word-break: break-all;">{{ $verificationUrl }}</a>
    </p>

    <p style="font-size: 12px; color: #888888;">
        This link expires in 24 hours.
    </p>
@endsection
