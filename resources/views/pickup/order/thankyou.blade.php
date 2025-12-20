@extends('layouts.app')
@section('content')

<h1 class="text-xl font-bold">予約完了</h1>
<p>受け取り日: {{ $order->pickup_date }}</p>
<p>受け取り時間: {{ $order->pickup_time }}</p>
<p class="mt-4">当日、指定時間に店舗へお越しください。</p>

@endsection