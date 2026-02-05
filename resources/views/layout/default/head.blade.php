<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $pageTitle . ' | ' ?? '' }} {{ config('app.name') }}</title>
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicons/apple-touch-icon.png') }}" />
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicons/favicon-32x32.png') }}" />
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicons/favicon-16x16.png') }}" />
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/favicons/favicon.ico') }}" />
<link rel="manifest" href="{{ asset('img/favicons/manifest.json') }}" />
<meta name="msapplication-TileImage" content="{{ asset('img/favicons/mstile-150x150.png') }}" />
<meta name="theme-color" content="#ffffff">
<meta name="baseurl" content="{{ url('/') }}">
<meta name="csrf_token" content="{{ csrf_token() }}" />
<script src="{{ asset('js/config.js') }}"></script>
<script src="{{ asset('js/anchor.min.js') }}"></script>
<script src="{{ asset('vendors/overlayscrollbars/OverlayScrollbars.min.js') }}"></script>
<link rel="preconnect" href="https://fonts.gstatic.com" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
<!--<link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet" />-->
<link href="{{ asset('vendors/overlayscrollbars/OverlayScrollbars.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/theme.min.css') }}" rel="stylesheet" id="style-default" />
<link href="{{ asset('css/user.min.css') }}" rel="stylesheet" id="user-style-default" />
<link href="{{ asset('css/common.css?v='.time()) }}" rel="stylesheet" />
<link href="{{ asset('vendors/jquery-confirm/jquery-confirm.min.css') }}" rel="stylesheet" />
<link href="{{ asset('vendors/toastify/toastify.min.css') }}" rel="stylesheet" />
<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
@stack('styles')