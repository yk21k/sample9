@extends('layouts.app')

@section('content')
    <div class="container text-center mt-5">
        <h2>お支払いが完了しました</h2>
        <p>ご購入ありがとうございました！</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-3">ホームに戻る</a>
    </div>
@endsection
