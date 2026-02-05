<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name') }}</title>
<link rel="apple-touch-icon" sizes="57x57" href="{{ asset('img/favicons/apple-icon-57x57.png') }}">
<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('img/favicons/apple-icon-60x60.png') }}">
<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('img/favicons/apple-icon-72x72.png') }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/favicons/apple-icon-76x76.png') }}">
<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('img/favicons/apple-icon-114x114.png') }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('img/favicons/apple-icon-120x120.png') }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('img/favicons/apple-icon-144x144.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/favicons/apple-icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicons/apple-icon-180x180.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicons/favicon-16x16.png') }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/favicons/favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('img/favicons/manifest.json') }}">
<meta name="msapplication-TileImage" content="{{ asset('img/favicons/mstile-150x150.png') }}" />
<meta name="theme-color" content="#ffffff">
<meta name="baseurl" content="{{ url('/') }}">
<script src="{{ asset('js/config.js') }}"></script>
<script src="{{ asset('vendors/overlayscrollbars/OverlayScrollbars.min.js') }}"></script>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
<!--<link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">-->
<link href="{{ asset('vendors/overlayscrollbars/OverlayScrollbars.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/theme.min.css') }}" rel="stylesheet" id="style-default"> 
<link href="{{ asset('css/user.min.css') }}" rel="stylesheet" id="user-style-default">
<link href="{{ asset('css/parsley.css') }}" rel="stylesheet" id="user-style-default">
<link href="{{ asset('css/common.css?v='.time()) }}" rel="stylesheet" />
<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
@stack('styles')