@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('front/css/custom98.css') }}">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register.pre_check') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="phone"
                                   class="col-md-4 col-form-label text-md-end">
                                {{ __('Phone Number') }}
                            </label>

                            <div class="col-md-6">
                                <input id="phone"
                                       type="tel"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       required
                                       autocomplete="tel"
                                       placeholder="09012345678">

                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- 利用目的・確認事項 --}}
                        <div class="row mb-4">
                            <label class="col-md-4 col-form-label text-md-end">
                                利用目的および確認事項
                            </label>

                            <div class="col-md-6">

                                @php
                                    $checks = [
                                        'email_usage' => 'メールアドレスは、本人確認・重要なお知らせ・パスワード再設定のために利用されることを理解しました。',
                                        'email_validity' => '登録するメールアドレスは、反社会的な活動等に用いられるものではなく、一時使用のものではありません。また、ご利用のアドレスを提供しているアプリやソフトウェアを日常的に利用したことがあり、<strong class="text-danger">場合によっては、受信したメールが迷惑メールに振り分けされることも認識</strong>し、<strong class="text-danger">重要なメールとして振り分けることもできるメールアプリがあることも認識した上で利用しているメールアドレスである</strong>ことを確認しました。',
                                        'phone_usage' => '電話番号は、本人確認および緊急時の連絡のために利用されることを理解しました。',
                                        'phone_validity' => '登録する電話番号は、反社会的な活動や違法行為を目的としたものではなく、本人が正当に利用している番号であることを確認しました。',
                                        'third_party' => '登録した個人情報は、本人の同意なく第三者に提供されません。ただし、法令に基づく要請や犯罪捜査等の正当な理由がある場合を除きます。'
                                    ];
                                @endphp

                               @foreach ($checks as $key => $label)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="agreements[{{ $key }}]"
                                               id="agree_{{ $key }}"
                                               {{ old("agreements.$key") ? 'checked' : '' }}>
                                        <label class="form-check-label" for="agree_{{ $key }}">
                                            {!! $label !!}
                                        </label>
                                        
                                    </div>
                                @endforeach
                                <strong>
                                    -----------------------------------------------------
                                </strong>
                                @error('agreements')
                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
