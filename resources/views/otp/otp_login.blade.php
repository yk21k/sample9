@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 500px;">
  <h2 class="mb-4">Pickup OTP ログイン</h2>

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form method="POST" action="{{ route('pickup.otp.login.verify') }}">
    @csrf

    <div class="mb-3">
      <label for="code" class="form-label">ワンタイムパスワード（6桁）</label>
      <input type="text" name="code" id="code" class="form-control" maxlength="6" required placeholder="例：123456">
      @error('code')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">ログインする</button>
  </form>

  <div class="text-center mt-3">
    <a href="{{ route('pickup.otp.index') }}">← 戻る</a>
  </div>
</div>
@endsection
