@extends('layouts.emails.app') @section('title', 'Reset Your Password')
@section('content')

<h2 style="margin: 0 0 20px; color: #212529; font-size: 24px">
    Reset Your Password
</h2>

<p>Hello {{ Str::ucfirst($user->name ?? 'User') }},</p>

<p>
    We received a request to reset the password for your
    <strong>{{ config('app.name') }}</strong>
    account.
</p>

<p>Click the button below to create a new password.</p>

<table
    cellpadding="0"
    cellspacing="0"
    border="0"
    width="100%"
    style="margin: 30px 0"
>
    <tr>
        <td align="center">
            <a
                href="{{ $url }}"
                style="
                    display: inline-block;
                    background: #0d6efd;
                    color: #ffffff;
                    text-decoration: none;
                    padding: 13px 28px;
                    border-radius: 5px;
                    font-size: 15px;
                    font-weight: 600;
                "
            >
                Reset Password
            </a>
        </td>
    </tr>
</table>

<div
    style="
        background: #fff3cd;
        border: 1px solid #ffecb5;
        border-radius: 5px;
        padding: 15px;
        color: #664d03;
        font-size: 13px;
    "
>
    <strong>Important:</strong>
    This password reset link will expire in
    <strong>{{ $expireText }}</strong>.
</div>

<p style="margin-top: 25px">
    If you did not request a password reset, you can safely ignore this email.
    Your password will remain unchanged.
</p>

@endsection
