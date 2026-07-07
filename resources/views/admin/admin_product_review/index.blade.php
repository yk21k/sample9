@extends('voyager::master')

@section('content')

<div class="container-fluid">

    <h1 class="page-title">
        商品審査一覧
    </h1>

    {{-- 集計 --}}
    <div class="row">

        <div class="col-md-3">
            <div class="panel panel-bordered">
                <div class="panel-body text-center">
                    <h2>{{ $summary['total'] }}</h2>
                    <div>審査待ち</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-bordered">
                <div class="panel-body text-center">
                    <h2>{{ $summary['new'] }}</h2>
                    <div>新規商品</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-bordered">
                <div class="panel-body text-center">
                    <h2>{{ $summary['update'] }}</h2>
                    <div>更新商品</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-bordered">
                <div class="panel-body text-center">
                    <h2>{{ $summary['movie'] }}</h2>
                    <div>動画あり</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-bordered">
                <div class="panel-body text-center">
                    <h2>
                        {{ $summary['ai_ng_images'] }}
                    </h2>
                    <div>
                        AI NG画像
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-bordered">
                <div class="panel-body text-center">
                    <h2>
                        {{ $summary['ai_ng_products'] }}
                    </h2>
                    <div>
                        AI NG商品
                    </div>
                </div>
            </div>
        </div>

    </div>

    <hr>

    {{-- 検索 --}}
    <div class="panel panel-bordered">

        <div class="panel-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-3">

                        <label>AI画像状態</label>

                        <select
                            name="image_ai_status"
                            class="form-control"
                        >

                            <option value="">
                                全て
                            </option>

                            <option
                                value="approved"
                                @selected(request('image_ai_status')=='approved')
                            >
                                OK
                            </option>

                            <option
                                value="warning"
                                @selected(request('image_ai_status')=='warning')
                            >
                                NGあり
                            </option>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>AI総合状態</label>

                        <select
                            name="overall_ai_status"
                            class="form-control"
                        >

                            <option value="">
                                全て
                            </option>

                            <option
                                value="approved"
                                @selected(request('overall_ai_status')=='approved')
                            >
                                OK
                            </option>

                            <option
                                value="warning"
                                @selected(request('overall_ai_status')=='warning')
                            >
                                NGあり
                            </option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label>&nbsp;</label>

                        <div>

                            <label>

                                <input
                                    type="checkbox"
                                    name="has_ai_ng"
                                    value="1"
                                    {{ request('has_ai_ng') ? 'checked' : '' }}
                                >

                                NG画像あり

                            </label>

                        </div>

                    </div>

                    <div class="col-md-2">

                        <label>&nbsp;</label>

                        <div>

                            <label>

                                <input
                                    type="checkbox"
                                    name="ai_completed"
                                    value="1"
                                    {{ request('ai_completed') ? 'checked' : '' }}
                                >

                                AI完了

                            </label>

                        </div>

                    </div>

                    <div class="col-md-2">

                        <label>&nbsp;</label>

                        <div>

                            <button
                                class="btn btn-primary btn-block"
                            >
                                検索
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- 一覧 --}}
    <div class="row">

        @forelse($drafts as $draft)

            @php

                $maxRisk =
                    $draft->imageReviews
                        ->max('risk_score');

            @endphp

            @if($maxRisk >= 80)

                <span class="label label-danger">
                    Risk {{ $maxRisk }}
                </span>

            @elseif($maxRisk >= 40)

                <span class="label label-warning">
                    Risk {{ $maxRisk }}
                </span>

            @elseif($maxRisk)

                <span class="label label-success">
                    Risk {{ $maxRisk }}
                </span>

            @endif

            <div class="col-md-6 col-lg-4">

                <div class="panel panel-bordered">

                    <div class="panel-body">

                        <h4>
                            {{ $draft->name }}
                        </h4>

                        <hr>

                        @if($draft->product_id)

                            <span class="label label-info">
                                更新商品
                            </span>

                        @else

                            <span class="label label-success">
                                新規商品
                            </span>

                        @endif

                        @if($draft->movie_file)

                            <span class="label label-warning">
                                動画あり
                            </span>

                        @endif



                        @if($draft->image_ai_status == 'approved')

                        <span class="label label-success">

                        AI OK

                        </span>

                        @elseif($draft->image_ai_status == 'warning')

                        <span class="label label-danger">

                        AI NG

                        </span>

                        @else

                        <span class="label label-default">

                        AI未実施

                        </span>

                        @endif

                        <br><br>

                        <p>
                            <strong>ID：</strong>
                            {{ $draft->id }}
                        </p>

                        <p>
                            <strong>ショップ：</strong>
                            {{ $draft->shop->name ?? '-' }}
                        </p>

                        <p>
                            <strong>作成者：</strong>
                            {{ $draft->originalCreator?->name ?? '-' }}
                        </p>

                        <p>
                            <strong>申請者：</strong>
                            {{ $draft->lastSubmitter?->name ?? '-' }}
                        </p>

                        <p>
                            <strong>申請日時：</strong><br>
                            {{ $draft->updated_at }}
                        </p>

                        <p>

                        <strong>AI画像：</strong>

                        {{ $draft->image_ai_ok_count }}

                        /

                        {{ $draft->image_ai_total_count }}

                        @if($draft->image_ai_ng_count)

                        <span class="label label-danger">

                        NG {{ $draft->image_ai_ng_count }}

                        </span>

                        @endif

                        </p>

                        <a
                            href="{{ route('admin.review.show', $draft->id) }}"
                            class="btn btn-primary btn-block"
                        >
                            詳細を見る
                        </a>

                    </div>

                </div>

            </div>

        @empty

            <div class="col-md-12">

                <div class="alert alert-info">

                    審査待ちの商品はありません

                </div>

            </div>

        @endforelse

    </div>

    <div class="text-center">
        {{ $drafts->links() }}
    </div>

</div>

@endsection