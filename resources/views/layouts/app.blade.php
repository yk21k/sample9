<!doctype html>

    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ Cookie::get('data-bs-theme'); }}">

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


    @stack('css')
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>


<body ontouchstart="">
        
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



    <!--====== Noscript ======-->
    <noscript>
        <div class="app-setting">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="app-setting__wrap">
                            <h1 class="app-setting__h1">JavaScript is disabled in your browser.</h1>
                            <span class="app-setting__text">Please enable JavaScript in your browser or upgrade to a JavaScript-capable browser.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </noscript>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).on('click', '#value', function(){
            var value = $(this).data('value');
            // console.log(value);
            $.ajax({
                headers: { 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
                },
                data:$("input[name='value']").val(),
                url:'/home',
                type:'post',
            }).done(function(res) {
                console.log(res);
                document.cookie = 'data-bs-theme=dark; path=/; max-age=60';
                if(document.documentElement.getAttribute('data-bs-theme')=='dark'){
                
                document.documentElement.setAttribute('data-bs-theme','light')

                }else{
                document.documentElement.setAttribute('data-bs-theme','dark')
                }

            })       
        })

    </script>
 
    <script src="{{ url('front/js/custom1.js') }}" defer></script>
    <script src="{{ url('front/js/custom2.js') }}" defer></script>
    <script src="{{ url('front/js/custom3.js') }}" defer></script>

    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>



    
</body>


</html>
