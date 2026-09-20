@extends('voyager::master')

@section('content')
<div class="page-content container-fluid">

    <h1 class="page-title">
        <i class="voyager-logbook"></i>
        ログ詳細
    </h1>

    <div class="panel panel-bordered">

        <div class="panel-heading">

            イベント情報

        </div>

        <div class="panel-body">

            <p>

                <strong>Event Group</strong>

                <br>

                {{ $auditLog->event_group }}

            </p>

            <p>

                <strong>Action</strong>

                <br>

                {{ $auditLog->action }}

            </p>

            <p>

                <strong>Description</strong>

                <br>

                {{ $auditLog->description }}

            </p>

        </div>

    </div>

    <div class="panel panel-bordered">
        <div class="panel-body">

            <p><strong>ユーザー:</strong>
                {{ $auditLog->user?->name ?? '-' }}
            </p>

            <p><strong>ロール：</strong>{{ $auditLog->role }}</p>

            <p><strong>操作：</strong>{{ $auditLog->action }}</p>

            <p><strong>説明：</strong>{{ $auditLog->description }}</p>

            <p><strong>対象：</strong>
            {{ $auditLog->target_type }} #{{ $auditLog->target_id }}
            </p>

            <p><strong>Product ID：</strong>{{ $auditLog->product_id }}</p>

            <p><strong>Draft ID：</strong>{{ $auditLog->draft_id }}</p>

            <p><strong>イベントグループ：</strong>

            {{ $auditLog->event_group }}

            </p>

            <p><strong>日時：</strong>

            {{ $auditLog->created_at }}

            </p>

            <p><strong>IP：</strong>

            {{ $auditLog->ip }}

            </p>

        </div>
    </div>

    <h3>変更内容</h3>

        @if($auditLog->diff_data)

        <table class="table table-bordered">

            <thead>

                <tr>

                <th>項目</th>

                <th>変更前</th>

                <th>変更後</th>

                </tr>

            </thead>

        <tbody>

            @foreach($auditLog->diff_data as $key => $change)

                <tr>

                    <td>

                        {{ $key }}

                    </td>

                    <td class="text-danger">

                        <pre>{{ json_encode($change['before'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

                    </td>

                    <td class="text-success">

                        <pre>{{ json_encode($change['after'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

                    </td>

                </tr>

            @endforeach

        </tbody>

        </table>

        @endif

    <hr>

        <h3>Before Data</h3>

        <pre>

        {{ json_encode($auditLog->before_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}

        </pre>

        <hr>

        <h3>After Data</h3>

        <pre>

        {{ json_encode($auditLog->after_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}

        </pre>    

</div>
<hr>

<h3>

同一イベントのログ

</h3>

<table class="table table-bordered">

    <thead>

        <tr>

            <th>日時</th>

            <th>Action</th>

            <th>Description</th>

            <th>対象</th>

        </tr>

    </thead>

    <tbody>

    @foreach($relatedLogs as $log)

        <tr>

            <td>

                {{ $log->created_at }}

            </td>

            <td>

                <span class="label label-info">

                    {{ $log->action }}

                </span>

            </td>

            <td>

                {{ $log->description }}

            </td>

            <td>

                {{ $log->target_type }}

                #

                {{ $log->target_id }}

            </td>

        </tr>

    @endforeach

    </tbody>

</table>

<hr>

@if($product)

<a
    href="{{ route('seller.products.timeline', $product) }}"
    class="btn btn-info">

    <i class="voyager-logbook"></i>

    商品ライフサイクル

</a>

@endif

@endsection