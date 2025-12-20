@extends('layouts.seller')

@section('content')
<div class="container">
  <h4>受け取り認証（OTP入力）</h4>

  <form method="POST" action="{{ route('seller.pickup.otp.verify') }}">
    @csrf
    <label>購入者提示のコード：</label>
    <input type="text" name="otp_code" class="form-control mb-3" maxlength="6" required>

    <button type="submit" class="btn btn-success">認証する</button>
  </form>

  @if(session('success'))
      <div class="alert alert-success mt-3">{{ session('success') }}</div>
  @endif
</div>
@endsection
