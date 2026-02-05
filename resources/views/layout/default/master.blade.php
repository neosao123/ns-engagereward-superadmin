<!DOCTYPE html>
<html lang="en">

<head>
    @include('layout.default.head')
</head>

<body>
    <main class="main" id="top">
        <div class="container-fluid" data-layout="container">
            @include('layout.default.sidebar')
            <div class="content">
                <!-- Top Bar -->
                @include('layout.default.topbar')
                <!-- End Top Bar -->
                <!-- Content -->
                @yield('content')
                <!-- End Content -->
				@include('layout.default.bottombar')
            </div>
        </div>
    </main>
    @include('layout.default.footer')
</body>

</html>