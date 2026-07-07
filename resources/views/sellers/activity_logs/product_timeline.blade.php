@extends('layouts.seller')

@section('content')
@php

$actions = [

    'draft_created'
        => '商品申請',

    'draft_updated'
        => '商品更新',

    'owner_approved'
        => 'オーナー承認',

    'owner_rejected'
        => 'オーナー差戻',

    'admin_approved'
        => '運営承認',

    'admin_rejected'
        => '運営却下',

    'product_published'
        => '商品公開',

    'product_unpublished'
        => '商品停止',

];

@endphp
<div class="container">

    <h2>

        商品タイムライン

    </h2>

    {{ $actions[$log->action]
    ?? $log->action }}
    
    <div class="card mb-4">

        <div class="card-body">

            <strong>
                {{ $product->name }}
            </strong>

            <br>

            Product ID:
            {{ $product->id }}

        </div>

    </div>

    @foreach($logs as $log)

        <div class="card mb-3">

            <div class="card-body">

                <div>

                    <strong>

                        {{ optional($log->user)->name }}

                    </strong>

                    ({{ $log->role }})

                </div>

                <div>

                    {{ $log->action }}

                </div>

                <div>

                    {{ $log->created_at }}

                </div>

            </div>

        </div>

    @endforeach

    {{ $logs->links() }}

</div>

@endsection