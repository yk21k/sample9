@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 500px;">
  <h3 class="mb-4 text-center">店舗スタッフログイン</h3>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('shop.login.post') }}">
    @csrf
    <div class="mb-3">
      <label for="email" class="form-label">メールアドレス</label>
      <input type="email" class="form-control" id="email" name="email" required autofocus>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">パスワード</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">ログイン</button>
  </form>
</div>
@endsection
