@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('front/css/custom97.css') }}">

<div class="wrapper">
    <div class="container px-3 py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8"> <!-- col-12: スマホ全幅 -->
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">{{ __('Login') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    name="password" required autocomplete="current-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>

                            <!-- Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">{{ __('Login') }}</button>
                                @if (Route::has('password.request'))
                                    <a class="btn btn-link text-center" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.footer') 
<script>
    
    document.addEventListener('DOMContentLoaded', () => {
        const footer = document.querySelector('.site-footer');
        if (!footer) return; // footer が存在しない場合はスキップ

        document.querySelectorAll('.card-body input, .card-body textarea').forEach(input => {
            input.addEventListener('focus', () => {
                footer.classList.remove('show'); // 一時的に非表示
            });
            input.addEventListener('blur', () => {
                footer.classList.add('show'); // フォーカス外れたら再表示
            });
        });
    });

   
</script>
@endsection
