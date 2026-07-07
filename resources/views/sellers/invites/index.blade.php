@extends('layouts.seller')

@section('content')
<h2>スタッフ招待一覧</h2>

<a href="{{ route('invite.create') }}" class="btn btn-primary mb-3">＋ スタッフ招待</a>

<table class="table">
    <thead>
        <tr>
            <th>名前</th>
            <th>メール</th>
            <th>状態</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
    @foreach($members as $member)
        <tr>
            <td>{{ $member->name }}</td>
            <td>{{ $member->email }}</td>

            <td>
                @if($member->user_id)
                    <span class="text-success">登録済み</span>
                @elseif($member->invite_token)
                    <span class="text-warning">招待中</span>
                @else
                    <span class="text-danger">失効</span>
                @endif
            </td>

            <td>
                @if(!$member->user_id)
                    <a href="{{ route('invite.reinvite', $member->id) }}" class="btn btn-sm btn-warning">再送</a>

                    <a href="{{ route('invite.expire', $member->id) }}" class="btn btn-sm btn-danger"
                       onclick="return confirm('失効しますか？')">失効</a>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection