@extends('layouts.seller')

@section('content')
<div class="container py-5 text-center">
  <h3 class="mb-4">ログアウト確認</h3>

  <p>本当にログアウトしますか？</p>

  <form method="POST" action="{{ route('shop_staff.logout') }}">
    @csrf
    <button type="submit" class="btn btn-danger px-4">ログアウトする</button>
    <a href="{{ route('shop.dashboard') }}" class="btn btn-secondary px-4 ms-2">キャンセル</a>
  </form>
</div>
@endsection
