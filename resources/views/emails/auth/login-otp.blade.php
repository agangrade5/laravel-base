@extends('layouts.emails.app') @section('title', 'Login OTP')
@section('content')

<h2 style="margin: 0 0 20px; color: #212529; font-size: 24px">
    Login Verification
</h2>

<p>Hello {{ Str::ucfirst($user->name ?? 'User') }},</p>

<p>
    We received a request to log in to your
    <strong>{{ config('app.name') }}</strong>
    account.
</p>

<p>Your One-Time Password (OTP) is:</p>

<!-- OTP Box -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center"
            style="
                padding: 20px;
                background-color: #f1f5ff;
                border: 1px solid #d9e2ff;
                border-radius: 6px;
            ">

            <span style="
                font-size: 32px;
                font-weight: bold;
                letter-spacing: 8px;
                color: #0d6efd;
            ">
                {{ $otp }}
            </span>

        </td>
    </tr>
</table>

<p style="
    margin: 20px 0 0;
    color: #555555;
    font-size: 14px;
    line-height: 1.6;
">
    This OTP is valid for
    <strong>{{ $otpExpireTime }} seconds</strong>
    and can be used only once.
</p>

<p style="
    margin: 15px 0 0;
    color: #555555;
    font-size: 14px;
    line-height: 1.6;
">
    For your security, please do not share this OTP
    with anyone.
</p>

<p style="
    margin: 20px 0 0;
    color: #777777;
    font-size: 13px;
    line-height: 1.6;
">
    If you did not request this login, you can safely
    ignore this email.
</p>

@endsection
