<nav class="navbar navbar-expand-md shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
                
                @if(!empty(Auth::user()->id))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('account.account', ['id'=>Auth::user()->id]) }}">Account</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home.auction') }}">Auction</a>
                </li>
                   
                @endif
                <li>
                    
                </li>
            </ul>


            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->

                <li class="nav-item">
                    
                </li>
                <li class="nav-item">
                    <form action="{{ route('products.search') }}" method="get">
                        <input name="query" placeholder="Enter Your Key Word" type="text">
                        <button type="submit" style="background: #fff; color: #535c97;"> Search </button>
                    </form>@csrf
                </li>

                <li class="nav-item">
                    <a class="nav-link p-0 m-0" href="{{ route('cart.index') }}">

                        <button type="button" class="btn btn-primary">
                          Cart <i class="fa fa-shopping-basket fa-2x" style="color:#B6A7A7 ;" aria-hidden="true"></i><span class="badge badge-light">@auth{{ Cart::session(auth()->id())->getContent()->count() }}@else 0 @endauth</span>
                        </button>
                    </a>
                </li>

                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif

                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->email }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>
                            
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @if(Auth::user()->role_id != 3)    
                                <a class="dropdown-item" href="{{ route('users.delete_confirm') }}">
                                   
                                    {{ __('Withdraw') }}
                                </a>
                            @else
                                <a class="dropdown-item" href="{{ route('users.delete_shop') }}">
                                   
                                    {{ __('Termination of Membership') }}
                                </a>
                                <form id="cancellation-request" action="" method="POST" class="d-none">@csrf</form>
                            @endif
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
{{--  display success message   --}} 
@if(session()->has('message'))
    <div class="alert alert-success" style="text-align:center" role="alert">
        {{ session('message') }}
    </div>
@endif

{{--  display success message   --}} 
@if(session()->has('error'))
    <div class="alert alert-danger" style="text-align:center" role="alert">
        {{ session('error') }}
    </div>
@endif





 
   