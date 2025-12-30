<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif !important;
            font-size: 16px;
            line-height: 1.5;
            color: #000000;
        }

        .im {
            color: #000000 !important;
        }
    </style>
    <title>{{ __('index.forgot_password') }} | {{ config('app.name') }}</title>
</head>

<body>
    <p style="color: #000000 !important;">{{ __('index.hey') }} {{ ucfirst($details['username']) }} &#128075;</p>

    <p style="color: #000000 !important;">{{ __('index.req_password') }}</p>
    <p style="color: #000000 !important;">&#128274; {{ __('index.expire_link') }}</p>
    <p><a href="{{ $details['link'] }}" target="_blank" style="text-align: center;">
            {{ __('index.reset_my_password') }}
        </a>
    </p>
    <p style="color: #000000 !important;">
        Didn’t request this? Just ignore this email or contact us at
        <a href="mailto:{{ env('SUPPORT_MAIL') }}">
            {{ env('SUPPORT_MAIL') }}
        </a>.
    </p>
    <p style="color: #000000 !important;">{{ __('index.cheers') }}, <br>{{ __('index.team') }}
        {{ config('app.name') }}
        &#128153;</p>

</body>

</html>
