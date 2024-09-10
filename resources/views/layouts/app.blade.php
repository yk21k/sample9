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
    <link rel="stylesheet" href="{{ asset('front/css/custom2.css') }}">
    <link rel="stylesheet" href="{{ asset('front/css/custom3.css') }}">
    <link rel="stylesheet" href="{{ asset('front/css/zoomple.css') }}">
    <link rel="stylesheet" href="{{ asset('front/css/magnifier.css') }}">
    @stack('css')
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/sass/product-detail.scss', 'resources/js/app.js'])
</head>
<body ontouchstart=””>
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
    <script src="{{ url('front/js/custom2.js') }}"></script>
    <script src="{{ url('front/js/custom3.js') }}"></script>
    <script src="{{ url('front/js/Event.js') }}"></script>
    <script src="{{ url('front/js/Magnifier.js') }}"></script>
    <script type="text/javascript">
        var evt = new Event(),
            m = new Magnifier(evt);

            m.attach({
                thumb: '#thumb',
                largeWrapper: 'preview',
                zoom: 7
            });
    </script>

  
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
</body>


</html>
