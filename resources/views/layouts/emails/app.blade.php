<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title', config('app.name'))</title>
    </head>
    <body
        style="
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
        "
    >
        <table
            width="100%"
            cellpadding="0"
            cellspacing="0"
            border="0"
            style="background-color: #f4f6f9"
        >
            <tr>
                <td align="center" style="padding: 40px 15px">
                    <table
                        width="600"
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                        style="
                            max-width: 600px;
                            width: 100%;
                            background: #ffffff;
                            border-radius: 8px;
                            overflow: hidden;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                        "
                    >
                        {{-- Header --}} @include('layouts.emails.header')
                        {{-- Content --}}
                        <tr>
                            <td
                                style="
                                    padding: 40px 35px;
                                    color: #212529;
                                    font-size: 15px;
                                    line-height: 1.7;
                                "
                            >
                                @yield('content')

                                <p style="margin-top: 25px">
                                    Regards,<br />
                                    <strong>{{ config('app.name') }}</strong>
                                </p>
                            </td>
                        </tr>

                        {{-- Footer --}} @include('layouts.emails.footer')
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
