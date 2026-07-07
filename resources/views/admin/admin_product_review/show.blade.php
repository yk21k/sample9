@extends('voyager::master')

@section('content')

@php
    use Illuminate\Support\Facades\Storage;

    $nameChanged =
        ($product->name ?? null)
        !=
        $draft->name;

    $priceChanged =
        ($product->price ?? null)
        !=
        $draft->price;

    $shippingChanged =
        ($product->shipping_fee ?? null)
        !=
        $draft->shipping_fee;

    $stockChanged =
        ($product->stock ?? null)
        !=
        $draft->stock;

    $descriptionChanged =
        ($product->description ?? null)
        !=
        $draft->description;

    $changedCount = collect([
        $nameChanged,
        $priceChanged,
        $shippingChanged,
        $stockChanged,
        $descriptionChanged,
    ])->filter()->count();    

@endphp

<div class="container-fluid">

    <h1>商品審査</h1>

    @if($product)
        <div class="alert alert-info">
            更新申請
        </div>
    @else
        <div class="alert alert-success">
            新規申請
        </div>
    @endif

    <div class="alert alert-info">

        変更項目数：
        {{ $changedCount }}

    </div>

    <h3>AI画像審査結果</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>画像</th>
                <th>判定</th>
                <th>スコア</th>
                <th>審査日時</th>
            </tr>
        </thead>

        <tbody>

        @foreach($reviews as $review)

            <tr>

                <td>{{ $review->image_type }}</td>

                <td>
                    @if($review->status === 'approved')
                        <span class="label label-success">
                            OK
                        </span>
                    @elseif($review->status === 'rejected')
                        <span class="label label-danger">
                            NG
                        </span>
                    @else
                        <span class="label label-warning">
                            審査中
                        </span>
                    @endif
                </td>

                <td>
                    {{ $review->risk_score }}
                </td>

                <td>
                    {{ $review->reviewed_at }}
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th width="20%">項目</th>
                <th width="40%">変更前</th>
                <th width="40%">変更後</th>
            </tr>
        </thead>

        <tbody>

            <tr class="{{ $nameChanged ? 'warning' : '' }}">

                <th>
                    商品名

                    @if($nameChanged)
                        <span class="label label-warning">
                            変更
                        </span>
                    @endif
                </th>
                <td>{{ $product->name ?? '-' }}</td>
                <td>{{ $draft->name }}</td>
            </tr>

            <tr class="{{ $priceChanged ? 'warning' : '' }}">

                <th>
                    価格

                    @if($priceChanged)
                        <span class="label label-warning">
                            変更
                        </span>
                    @endif
                </th>

                <td>{{ $product->price ?? '-' }}</td>
                <td>{{ $draft->price }}</td>
            </tr>

            <tr class="{{ $shippingChanged ? 'warning' : '' }}">

                <th>送料</th>

                <td>{{ $product->shipping_fee ?? '-' }}</td>
                <td>{{ $draft->shipping_fee }}</td>
            </tr>

            <tr class="{{ $stockChanged ? 'warning' : '' }}">

                <th>在庫</th>

                <td>{{ $product->stock ?? '-' }}</td>
                <td>{{ $draft->stock }}</td>
            </tr>

            <tr class="{{ $descriptionChanged ? 'warning' : '' }}">

                <th>説明</th>

                <td style="white-space: pre-wrap;">
                    {{ $product->description ?? '-' }}
                </td>

                <td style="white-space: pre-wrap;">
                    {{ $draft->description }}
                </td>
            </tr>

        </tbody>

    </table>

    <div class="card mb-3">
        <div class="card-body">

            <strong>申請者</strong><br>

            {{ $draft->creator?->name }}

        </div>
    </div>

    <div class="panel panel-bordered">

        <div class="panel-heading">

            <strong>

                AI審査サマリー

            </strong>

        </div>

        <div class="panel-body">

            <div class="row">

                <div class="col-md-3">

                    <h4>総合判定</h4>

                    @if($aiSummary['status']=='approved')

                        <span class="label label-success">

                            OK

                        </span>

                    @elseif($aiSummary['status']=='warning')

                        <span class="label label-danger">

                            Warning

                        </span>

                    @else

                        <span class="label label-default">

                            未実施

                        </span>

                    @endif

                </div>

                <div class="col-md-3">

                    <h4>画像AI</h4>

                    {{ $aiSummary['ok'] }}

                    /

                    {{ $aiSummary['total'] }}

                </div>

                <div class="col-md-3">

                    <h4>NG画像</h4>

                    {{ $aiSummary['ng'] }}

                </div>

                <div class="col-md-3">

                    <h4>最大Risk</h4>

                    {{ $aiSummary['maxRisk'] ?? '-' }}

                </div>

            </div>

        </div>

    </div>

    @if($aiSummary['ng'])

    <div class="alert alert-danger">

        <strong>

            AIが{{ $aiSummary['ng'] }}枚の画像をNG判定しています。

        </strong>

        人の目で確認してください。

    </div>

    @endif
    
    <div class="card mb-3">
        <div class="card-header">
            申請情報
        </div>

        <div class="card-body">

            <table class="table table-sm">

                <tr>
                    <th width="200">
                        商品作成者
                    </th>
                    <td>
                        {{ $draft->originalCreator?->name ?? '-' }}
                        (ID: {{ $draft->original_created_by ?? '-' }})
                    </td>
                </tr>

                <tr>
                    <th>
                        今回の申請者
                    </th>
                    <td>
                        {{ $draft->lastSubmitter?->name ?? '-' }}
                        (ID: {{ $draft->last_submitted_by ?? '-' }})
                    </td>
                </tr>

                <tr>
                    <th>
                        オーナー承認者
                    </th>
                    <td>
                        {{ optional($draft->ownerReviewer)->name ?? '-' }}
                        (ID: {{ $draft->owner_reviewed_by ?? '-' }})
                    </td>
                </tr>

            </table>

        </div>
    </div>

    <hr>

    <h3>画像比較</h3>

    @foreach([
        'cover_img',
        'cover_img2',
        'cover_img3'
    ] as $field)

        @php

            $imageChanged =
                ($product->$field ?? null)
                !==
                ($draft->$field ?? null);

        @endphp

        <div class="panel {{ $imageChanged ? 'panel-warning' : 'panel-default' }}">

            <div class="panel-heading">

                {{ $field }}

                @if($imageChanged)
                    <span class="label label-warning">
                        変更あり
                    </span>
                @endif

            </div>

            <div class="panel-body">

                <div class="row">

                    {{-- 旧画像 --}}
                    <div class="col-md-6">

                        <h4>変更前</h4>

                        @if($product && $product->$field)

                            <img
                                src="{{ Storage::disk('s3')->url($product->$field) }}"
                                style="
                                    max-width:300px;
                                    max-height:300px;
                                    border:4px solid {{ $imageChanged ? '#f0ad4e' : '#ccc' }};
                                "
                            >

                            @if($imageChanged)

                                <div class="alert alert-warning">

                                    画像が変更されています

                                </div>

                            @endif

                        @else

                            <div class="alert alert-warning">
                                画像なし
                            </div>

                        @endif

                    </div>

                    {{-- 新画像 --}}
                    <div class="col-md-6">

                        <h4>変更後</h4>

                        @if($draft->$field)

                            <img
                                src="{{ Storage::disk('s3')->url($draft->$field) }}"
                                style="
                                    max-width:300px;
                                    max-height:300px;
                                    border:1px solid #ccc;
                                "
                            >

                        @else

                            <div class="alert alert-warning">
                                画像なし
                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    @endforeach

    <hr>

    @php
        $movieChanged =
            ($product->movie_file ?? null)
            !==
            ($draft->movie_file ?? null);
    @endphp

    <h3>動画</h3>

    <div class="row">

        {{-- 変更前 --}}
        <div class="col-md-6">

            <div class="card {{ $movieChanged ? 'border-danger' : '' }}">
                <div class="card-header">
                    変更前動画
                </div>

                <div class="card-body">

                    @if($product?->movie_file)

                        <video width="100%" controls>
                            <source
                                src="{{ asset('storage/'.$product->movie_file) }}">
                        </video>

                    @else

                        <p class="text-muted">
                            動画なし
                        </p>

                    @endif

                </div>
            </div>

        </div>

        {{-- 変更後 --}}
        <div class="col-md-6">

            <div class="card {{ $movieChanged ? 'border-danger' : '' }}">
                <div class="card-header">
                    変更後動画
                </div>

                <div class="card-body">

                    @if($draft->movie_file)

                        <video width="100%" controls>
                            <source
                                src="{{ asset('storage/'.$draft->movie_file) }}">
                        </video>

                    @else

                        <p class="text-muted">
                            動画なし
                        </p>

                    @endif

                </div>
            </div>

        </div>

    </div>

    @if($movieChanged)

        <div class="alert alert-warning mt-3">
            動画が変更されています
        </div>

    @endif

    <form
        method="POST"
        action="{{ route('admin.review.approve', $draft) }}"
    >

        @csrf

        <button
            class="btn btn-success"
            type="submit"
        >
            ss承認
        </button>

    </form>

    <hr>

    <div class="form-group">

        <label>
            管理者コメント
        </label>

        <textarea
            id="admin_comment"
            class="form-control"
            rows="5"
        ></textarea>

    </div>

    <form
        method="POST"
        action="{{ route('admin.review.revision', $draft) }}"
    >

        @csrf

        <input
            type="hidden"
            name="admin_comment"
            id="revision_comment"
        >

        <button
            type="submit"
            class="btn btn-warning"
            onclick="
                document.getElementById('revision_comment').value =
                document.getElementById('admin_comment').value;
            "
        >
            差戻
        </button>

    </form>

    <form
        method="POST"
        action="{{ route('admin.review.reject', $draft) }}"
    >

        @csrf

        <input
            type="hidden"
            name="admin_comment"
            id="reject_comment"
        >

        <button
            type="submit"
            class="btn btn-danger"
            onclick="
                document.getElementById('reject_comment').value =
                document.getElementById('admin_comment').value;
            "
        >
            却下
        </button>

    </form>

</div>

@endsection