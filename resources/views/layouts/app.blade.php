<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('front/css/custom1.css') }}">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">

        <!-- header part -->
            <div class="wrapper">
                @include('layouts.header')
            </div>    

            <div class="wrapper" style="margin-left: 300px;">
                <div class="wrapper">
                    @yield('content')
                </div>
            </div>    

        <!-- footer part -->
            <div class="wrapper"style="margin-left: 300px;">
                @include('layouts.footer')
            </div>    
    </div>

    <script src="{{ url('front/js/custom1.js') }}"></script>
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
    
</body>


</html>
