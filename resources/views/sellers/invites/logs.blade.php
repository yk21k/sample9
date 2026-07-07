@extends('layouts.seller')

@section('content')
<h2>招待ログ</h2>

<table class="table">
    <thead>
        <tr>
            <th>メール</th>
            <th>状態</th>
            <th>日時</th>
        </tr>
    </thead>
    <tbody>
    @foreach($logs as $log)
        <tr>
            <td>{{ $log->email }}</td>
            <td>
                @if($log->status === 'sent')
                    <span class="text-primary">送信</span>
                @elseif($log->status === 'resent')
                    <span class="text-warning">再送</span>
                @elseif($log->status === 'expired')
                    <span class="text-danger">失効</span>
                @elseif($log->status === 'registered')
                    <span class="text-success">登録完了</span>
                @endif
            </td>
            <td>{{ $log->sent_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $logs->links() }}
@endsection