@extends('voyager::master')

@section('content')

<div class="page-content container-fluid">

    <h1 class="page-title">

        <i class="voyager-logbook"></i>

        商品ライフサイクル

    </h1>

    <div class="panel panel-bordered">

        <div class="panel-heading">

            商品情報

        </div>

        <div class="panel-body">

            <table class="table">

                <tr>

                    <th width="180">Product ID</th>

                    <td>{{ $product->id }}</td>

                </tr>

                <tr>

                    <th>商品名</th>

                    <td>{{ $product->name }}</td>

                </tr>

                <tr>

                    <th>現在状態</th>

                    <td>

                        {{ $product->review_status }}

                    </td>

                </tr>

            </table>

        </div>

    </div>


    @foreach($eventGroups as $eventGroup => $logs)

        @php

            $first = $logs->first();

        @endphp

        <div class="panel panel-bordered">

            <div class="panel-heading">

                <strong>

                    {{ $first->description }}

                </strong>

                <span class="pull-right">

                    {{ $first->created_at }}

                </span>

            </div>

            <div class="panel-body">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th width="180">Action</th>

                            <th width="150">User</th>

                            <th width="120">Role</th>

                            <th>Description</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($logs as $log)

                            <tr>

                                <td>

                                    <span class="label label-info">

                                        {{ $log->action }}

                                    </span>

                                </td>

                                <td>

                                    {{ optional($log->user)->name }}

                                </td>

                                <td>

                                    {{ $log->role }}

                                </td>

                                <td>

                                    {{ $log->description }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>


                @foreach($logs as $log)

                    @if(!empty($log->diff_data))

                        <h4>

                            {{ $log->action }}

                        </h4>

                        <table class="table table-striped">

                            <thead>

                                <tr>

                                    <th>項目</th>

                                    <th>変更前</th>

                                    <th>変更後</th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($log->diff_data as $key => $change)

                                    <tr>

                                        <td>

                                            {{ $key }}

                                        </td>

                                        <td>

<pre>{{ json_encode($change['before'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

                                        </td>

                                        <td>

<pre>{{ json_encode($change['after'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    @endif

                @endforeach

            </div>

        </div>

    @endforeach

</div>

@endsection