
@if(auth()->user()===null)
    404 not found 
@elseif(auth()->user()->role_id===2)
    <h2>404 because authentication is not possible</h2>
@else   
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('front/css/custom8.css') }}">
    <link rel="stylesheet" href="{{ asset('front/css/custom9.css') }}">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .disabled-coupon-link {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
            position: relative;
        }
    </style>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                        @can('customer')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shops.create') }}">Open Your Shop</a>
                            </li>
                        @endcan
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">

                        @can('customer')
                        <li class="nav-item mr-2">
                            <a class="nav-link p-0 m-0" href="{{ route('cart.index') }}">
                                <i class="fas fa-cart-arrow-down text-success fa-2x"></i>
                                <div class="badge badge-danger">
                                    @auth
                                    {{Cart::session(auth()->id())->getContent()->count()}}
                                    @else
                                    0
                                    @endauth
                                </div>
                            </a>
                        </li>
                        @endcan

                        <!-- Authentication Links -->
                        @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                        @endif
                        @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        {{-- display success message --}}
        @if(session()->has('message'))
        <div class="alert alert-success text-center" role="alert">
            {{session('message')}}
        </div>
        @endif

        {{-- display error message --}}

        @if(session()->has('error'))
        <div class="alert alert-danger text-center" role="alert">
            {{session('error')}}
        </div>
        @endif

        <main class="py-4 container-fluid bg-secondary text-white">
            <div class="row">
                <div class="col-3">
                    <div class="sidebar_fixed">
                        <div class="list-group">
                            <a href="/seller" class="list-group-item list-group-item-action active">Dashboard</a>

                            <a href=" {{ route('seller.shop.shop_setting') }} " class="list-group-item list-group-item-action">Your Shop</a>
                            
                            <a href=" {{url('/seller/orders')}} " class="list-group-item list-group-item-action">Orders</a>

                            <a href=" {{url('/admin/shops')}} " class="list-group-item list-group-item-action">Go to Shop</a>

                            @php
                                $orders = App\Models\SubOrder::where('seller_id', auth()->id())->first();
                            @endphp
                            @if($orders)
                                <a href=" {{ route('order.make_coupon') }} " class="list-group-item list-group-item-action">Create Shop Coupon</a>
                            @else
                                <a href="" class="list-group-item list-group-item-action">Create Shop Coupon<br>（初決済後にご利用いただけます。）</a>    
                            @endif

                            <a href=" {{url('/seller/calendar')}}" class="list-group-item list-group-item-action">Shop Calendar</a>

                            <a href=" {{url('/seller/shop_desplay')}}" class="list-group-item list-group-item-action">Shop Desplay</a>

                            <a href=" {{ url('/seller/shop_charts') }} " class="list-group-item list-group-item-action">Shop Charts</a>

                            <a href=" {{ url('/seller/shop_mail') }} " class="list-group-item list-group-item-action">Shop Mail</a>

                            <a href=" {{ url('/seller/shop_setting') }} " class="list-group-item list-group-item-action">Shop Setting</a>

                            <div class="card mb-4">
                                <div class="card-body">
                                    @if (auth()->user()->stripe_account_id)
                                        <div class="alert alert-success d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Stripeに接続済み</strong><br>
                                                <span class="text-muted">アカウントID: {{ auth()->user()->stripe_account_id }}</span>
                                            </div>
                                            <a href="{{ route('stripe.connect') }}" class="btn btn-outline-secondary btn-sm">
                                                再接続
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Stripe未接続</strong><br>
                                                <span class="text-muted">売上の受け取りには接続が必要です。</span>
                                            </div>
                                            <a href="{{ route('stripe.connect') }}" class="btn btn-primary btn-sm">
                                                Stripeと接続する
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <a href=" {{ url('/seller/shop_auction_orders') }} " class="list-group-item list-group-item-action">オークション　オーダー</a>

                        </div>
                    </div>
                </div>

                <div class="col-9">
                    @yield('content')
                    
                </div>

            </div>
        </main>
    </div>
</body>

    <!--====== Noscript ======-->
    <style>
        /* JavaScriptが無効なときにmain要素やbodyを非表示にする */
        .no-js body {
            display: none !important;
        }
    </style>

    <noscript>
        <style>
            body {
                display: none !important;
            }
            html::before {
                content: "このサイトはJavaScriptが必要です。JavaScriptを有効にしてください。";
                display: block;
                padding: 2rem;
                font-size: 1.5rem;
                color: red;
                background: #fff3f3;
                text-align: center;
            }
        </style>
    </noscript>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

</html>

@endif