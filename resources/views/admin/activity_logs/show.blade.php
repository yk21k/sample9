@extends('voyager::master')

@section('content')
<div class="page-content container-fluid">

    <h1 class="page-title">
        <i class="voyager-logbook"></i>
        ログ詳細
    </h1>

    <div class="panel panel-bordered">
        <div class="panel-body">

            <p><strong>ユーザー:</strong>
                {{ optional(\App\Models\User::find($log->user_id))->name }}
            </p>

            <p><strong>ロール:</strong> {{ $log->role }}</p>
            <p><strong>操作:</strong> {{ $log->action }}</p>
            <p><strong>対象:</strong> {{ $log->target_type }}#{{ $log->target_id }}</p>
            <p><strong>日時:</strong> {{ $log->created_at }}</p>

        </div>
    </div>

    <h4>変更内容</h4>

    @if(!empty($log->changes))
        <table class="table table-bordered">
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
                        <td class="text-danger">{{ $change['before'] }}</td>
                        <td class="text-success">{{ $change['after'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
@endsection