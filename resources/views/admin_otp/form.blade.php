@extends('layouts.seller')

@section('content')
<div class="container">
    <h2>OTP 認証</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('otp.verify') }}" method="POST">
        @csrf
        <input type="text" name="otp" class="form-control" placeholder="6桁のコード" required>

        @error('otp')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <button class="btn btn-primary mt-3">認証する</button>
    </form>

    <form action="{{ route('otp.send') }}" method="POST">
        @csrf
        <button class="btn btn-secondary mt-3">OTP を再送信</button>
    </form>
</div>
@endsection
