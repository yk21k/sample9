@extends('layouts.app')

@section('content')

<h2>決済が完了しました</h2>
<p>ご利用ありがとうございます！</p>
<a href="{{ url('/') }}" class="btn btn-primary">トップへ戻る</a>

@endsection
