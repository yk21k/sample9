@extends('layouts.seller')

@section('content')
<div class="container">

    <h2 class="mb-4">操作ログ一覧</h2>

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

    <table class="table table-bordered table-striped bg-white text-dark">
        <thead>
            <tr>
                <th>日時</th>
                <th>ユーザー</th>
                <th>ロール</th>
                <th>操作</th>
                <th>対象</th>
                <th>詳細</th>
                <th>タイムライン</th>
            </tr>
        </thead>

        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>

                    <td>
                        {{ optional(\App\Models\User::find($log->user_id))->name }}
                    </td>

                    <td>
                        <span class="badge bg-secondary">
                            {{ $log->role }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-success">
                            {{ $log->action }}
                        </span>
                    </td>

                    <td>
                        {{ $log->target_type }}#{{ $log->target_id }}
                    </td>

                    <td>
                        <a href="{{ route('seller.activity_logs.show', $log->id) }}"
                           class="btn btn-sm btn-primary">
                            詳細
                        </a>
                    </td>

                    <td>
                        <a
                            href="{{ route(
                                'seller.products.timeline',
                                $product
                            ) }}"
                            class="btn btn-sm btn-info"
                        >

                            タイムライン

                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $logs->links() }}

</div>
@endsection