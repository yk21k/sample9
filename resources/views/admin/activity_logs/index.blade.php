@extends('voyager::master')

@section('content')
<div class="page-content container-fluid">

    <h1 class="page-title">
        <i class="voyager-logbook"></i>
        操作ログ
    </h1>

    <form method="GET" class="mb-4">

        <div class="row">

            <div class="col-md-2">
                <input type="text" name="user_id" class="form-control"
                    placeholder="user_id"
                    value="{{ request('user_id') }}">
            </div>

            <div class="col-md-2">
                <input type="text" name="action" class="form-control"
                    placeholder="action"
                    value="{{ request('action') }}">
            </div>

            <div class="col-md-2">
                <input type="text" name="target_type" class="form-control"
                    placeholder="Productなど"
                    value="{{ request('target_type') }}">
            </div>

            <div class="col-md-2">
                <input type="text" name="target_id" class="form-control"
                    placeholder="ID"
                    value="{{ request('target_id') }}">
            </div>

            <div class="col-md-2">
                <input type="date" name="from" class="form-control"
                    value="{{ request('from') }}">
            </div>

            <div class="col-md-2">
                <input type="date" name="to" class="form-control"
                    value="{{ request('to') }}">
            </div>

        </div>

        <div class="mt-2">
            <button class="btn btn-primary">検索</button>

            <a href="{{ route('admin.activity_logs.index') }}"
               class="btn btn-secondary">
                リセット
            </a>
        </div>

    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>日時</th>
                <th>ユーザー</th>
                <th>ロール</th>
                <th>操作</th>
                <th>対象</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>

                    <td>
                        {{ optional(\App\Models\User::find($log->user_id))->name }}
                    </td>

                    <td>{{ $log->role }}</td>

                    <td>
                        <span class="label label-info">
                            {{ $log->action }}
                        </span>
                    </td>

                    <td>
                        {{ $log->target_type }}#{{ $log->target_id }}
                    </td>

                    <td>
                        <a href="{{ route('admin.activity_logs.show', $log->id) }}"
                           class="btn btn-sm btn-primary">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $logs->links() }}


</div>
@endsection