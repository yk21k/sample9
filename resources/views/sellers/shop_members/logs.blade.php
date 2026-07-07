@extends('layouts.seller')
<h2>招待履歴</h2>

@foreach($logs as $log)
    <div style="border-bottom:1px solid #ccc; margin-bottom:10px;">
        <strong>{{ $log->status }}</strong><br>
        {{ $log->sent_at }}<br>
        token: {{ $log->token }}
    </div>
@endforeach