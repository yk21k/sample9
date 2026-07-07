@extends('layouts.seller')

@section('content')
<div class="container">

    <h2 class="mb-4">ログ詳細</h2>

    <div class="card mb-4 text-dark">
        <div class="card-body">
            <p><strong>ユーザー:</strong>
                {{ optional(\App\Models\User::find($log->user_id))->name }}
            </p>

            <p><strong>ロール:</strong> {{ $log->role }}</p>
            <p><strong>操作:</strong> {{ $log->action }}</p>
            <p><strong>対象:</strong> {{ $log->target_type }}#{{ $log->target_id }}</p>
            <p><strong>日時:</strong> {{ $log->created_at }}</p>
        </div>
    </div>

    <h4 class="text-white">変更内容</h4>

    @if(!empty($log->changes))
        <table class="table table-bordered bg-white text-dark">
            <thead>
                <tr>
                    <th>項目</th>
                    <th>変更前</th>
                    <th>変更後</th>
                </tr>
            </thead>

            <tbody>
                @foreach($log->changes as $key => $change)
                    <tr>
                        <td>{{ $key }}</td>

                        <td class="text-danger">
                            {{ is_array($change['before']) ? json_encode($change['before']) : $change['before'] }}
                        </td>

                        <td class="text-success">
                            {{ is_array($change['after']) ? json_encode($change['after']) : $change['after'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-white">変更なし</p>
    @endif

</div>
@endsection