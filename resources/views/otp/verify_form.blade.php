@extends('layouts.app')

@section('content')
<div class="container text-center">
  <h4>ワンタイムパスワード認証</h4>
  <p>発行された6桁のOTPを入力してください。</p>

  <form method="POST" action="{{ route('pickup.otp.verify') }}" class="mt-4">
    @csrf
    <input type="text" name="code" maxlength="6" required class="form-control text-center mb-3" placeholder="例: 123456">
    <button type="submit" class="btn btn-primary">認証する</button>
  </form>

  @if(session('error'))
    <p class="text-danger mt-3">{{ session('error') }}</p>
  @endif
</div>
@endsection
