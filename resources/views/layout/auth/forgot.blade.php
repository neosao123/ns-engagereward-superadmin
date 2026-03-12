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
    {!! $details['body'] !!}
</body>

</html>
