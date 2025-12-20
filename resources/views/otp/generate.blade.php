@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>店舗受け取り用 ワンタイムパスワード（OTP）</h3>
    <hr>
    @if (session('info'))
        <div class="alert alert-info mt-3">
            {{ session('info') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="card p-4 shadow-sm text-center">
        <h4 class="fw-bold mb-3">OTPコード</h4>
        <h1 class="display-3 text-primary">{{ $otp }}</h1>

        <p class="text-muted mt-2">
            有効期限：{{ $expires_at->format('Y-m-d H:i') }}
        </p>

        <p class="text-secondary mt-3">
            ※ このコードを店舗スタッフに提示してください。
        </p>

        <div class="mt-4">
            <a href="{{ route('pickup.otp.login.form') }}" class="btn btn-primary">
                このOTPでログイン
            </a>
        </div>

        <div class="mt-4">
            <a href="{{ route('pickup.orders.index') }}" class="btn btn-secondary">戻る</a>
        </div>
    </div>
</div>
@endsection
