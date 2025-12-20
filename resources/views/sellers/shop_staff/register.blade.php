@extends('layouts.seller')
@section('content')
<div class="container py-5" style="max-width: 500px;">
  <h3 class="mb-4 text-center">店舗スタッフ登録</h3>
  登録期限は、一カ月のため、再取得のため、ご登録ください。
  <form method="POST" action="{{ route('seller.pickup.shop.register.store') }}">
    @csrf
    <div class="mb-3">
      <label for="name" class="form-label">名前</label>
      <input type="text" class="form-control" id="name" name="name" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">メールアドレス</label>
      <input type="email" class="form-control" id="email" name="email" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">パスワード</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <div class="mb-3">
      <label for="password_confirmation" class="form-label">パスワード（確認）</label>
      <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">登録</button>
  </form><br>
  <a style="color:whitesmoke;" href="{{ route('shop_staff.login', ['token' => $token]) }}">
    店員さんのログイン画面へ
  </a>
  <a style="color:whitesmoke ;" href="{{ route('seller.admin.staff.qr') }}">店員さんのログイン画面QR</a>

</div>


@endsection
