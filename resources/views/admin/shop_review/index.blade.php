@extends('voyager::master')

@section('content')

<div class="container">

<h2>出店審査一覧</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>申請者</th>
            <th>種別</th>
            <th>対象ショップ</th>
            <th>申請日時</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    @foreach($applications as $app)
        <tr>
            <td>{{ $app->id }}</td>
            <td>{{ $app->user->name ?? '-' }}</td>
            <td>{{ $app->type }}</td>
            <td>{{ $app->shop->name ?? '新規' }}</td>
            <td>{{ $app->created_at }}</td>
            <td>
                <a href="/admin/shop-applications/{{ $app->id }}" class="btn btn-primary btn-sm">
                    審査
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $applications->links() }}

</div>

@endsection