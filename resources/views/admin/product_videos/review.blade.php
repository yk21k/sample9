@extends('voyager::master')

@section('content')

<div class="container-fluid">


    <h1 class="page-title">
        商品動画 管理者審査
    </h1>

    {{-- 商品情報 --}}
    <div class="panel panel-bordered">

        <div class="panel-heading">
            <h3 class="panel-title">
                商品情報
            </h3>
        </div>

        <div class="panel-body">

            <dl class="dl-horizontal">

                <dt>商品ID</dt>
                <dd>{{ $video->product_id }}</dd>

                <dt>商品名</dt>
                <dd>
                    @if ($video->product)
                        {{ $video->product->name }}
                    @else
                        <span class="text-danger">
                            商品情報が取得できません
                        </span>
                    @endif
                </dd>

            </dl>

        </div>

    </div>


    {{-- 動画情報 --}}
    <div class="panel panel-bordered">

        <div class="panel-heading">
            <h3 class="panel-title">
                動画情報
            </h3>
        </div>

        <div class="panel-body">

            <dl class="dl-horizontal">

                <dt>動画ID</dt>
                <dd>{{ $video->id }}</dd>

                <dt>動画タイトル</dt>
                <dd>{{ $video->title }}</dd>

            </dl>


            @if ($previewUrl)
                <div class="form-group">

                    <label>動画プレビュー</label>

                    <div>
                        <video
                            controls
                            preload="metadata"
                            style="max-width: 100%; width: 480px; max-height: 420px;"
                        >
                            <source
                                src="{{ $previewUrl }}"
                                type="video/mp4"
                            >

                            お使いのブラウザは動画再生に対応していません。
                        </video>
                    </div>

                </div>
            @endif

        </div>

    </div>


    {{-- 工程・審査状態 --}}
    <div class="panel panel-bordered">

        <div class="panel-heading">
            <h3 class="panel-title">
                工程・審査状態
            </h3>
        </div>

        <div class="panel-body">

            <dl class="dl-horizontal">

                <dt>AI審査</dt>
                <dd>{{ $video->ai_status }}</dd>

                <dt>動画加工</dt>
                <dd>{{ $video->process_status }}</dd>

                <dt>プレビュー</dt>
                <dd>{{ $video->preview_status }}</dd>

                <dt>出品者確認</dt>
                <dd>{{ $video->seller_review_status }}</dd>

                <dt>管理者審査</dt>
                <dd>{{ $video->review_status }}</dd>

                <dt>YouTube</dt>
                <dd>{{ $video->youtube_status }}</dd>

                @if ($video->youtube_video_id)
                    <dt>YouTube動画ID</dt>
                    <dd>{{ $video->youtube_video_id }}</dd>
                @endif

                @if ($video->youtube_uploaded_at)
                    <dt>アップロード日時</dt>
                    <dd>{{ $video->youtube_uploaded_at }}</dd>
                @endif

            </dl>

        </div>

    </div>

    {{-- 管理者操作 --}}
    <div class="panel panel-bordered">

        <div class="panel-heading">
            <h3 class="panel-title">
                管理者操作
            </h3>
        </div>

        <div class="panel-body">

            @if (
                $video->review_status
                    === \App\Models\ProductVideoDraft::REVIEW_PENDING
            )

                <form
                    method="POST"
                    action="{{ route(
                        'admin.product-video-drafts.approve',
                        $video->id
                    ) }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-success"
                        onclick="return confirm(
                            'この商品動画を承認してYouTube工程へ進めますか？'
                        );"
                    >
                        管理者承認
                    </button>
                </form>

            @elseif (
                $video->review_status
                    === \App\Models\ProductVideoDraft::REVIEW_APPROVED
            )

                <div class="alert alert-success">
                    管理者承認済みです。
                </div>

            @endif


            {{-- 管理者審査NG --}}
            @if (
                $video->review_status
                    === \App\Models\ProductVideoDraft::REVIEW_PENDING
            )

                <hr>

                <div class="form-group">

                    <h4>掲載不可</h4>

                    <p>
                        この動画を掲載不可として処理します。
                        掲載不可後は修正・再審査ではなく、
                        出品者が新しい動画を最初から登録します。
                    </p>

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.product-video-drafts.reject',
                            $video->id
                        ) }}"
                    >

                        @csrf

                        <div class="form-group">

                            <label for="review_reject_reason">
                                掲載不可理由
                            </label>

                            <select
                                name="review_reject_reason"
                                id="review_reject_reason"
                                class="form-control"
                                required
                            >
                                <option value="">
                                    選択してください
                                </option>

                                <option value="商品と無関係">
                                    商品と無関係
                                </option>

                                <option value="不適切な内容">
                                    不適切な内容
                                </option>

                                <option value="権利侵害の疑い">
                                    権利侵害の疑い
                                </option>

                                <option value="虚偽・誤認を招く内容">
                                    虚偽・誤認を招く内容
                                </option>

                                <option value="品質上の問題">
                                    品質上の問題
                                </option>

                                <option value="その他">
                                    その他
                                </option>
                            </select>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-danger"
                            onclick="return confirm(
                                'この動画を掲載不可にします。よろしいですか？'
                            );"
                        >
                            掲載不可にする
                        </button>

                    </form>

                </div>

            @endif


            @if (
                $video->review_status
                    === \App\Models\ProductVideoDraft::REVIEW_APPROVED
                &&
                $video->youtube_status
                    === \App\Models\ProductVideoDraft::YOUTUBE_FAILED
            )

                <hr>

                <form
                    method="POST"
                    action="{{ route(
                        'admin.product-video-drafts.youtube.retry',
                        $video->id
                    ) }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-warning"
                        onclick="return confirm(
                            'YouTubeへの再アップロードを開始しますか？'
                        );"
                    >
                        YouTube再アップロード
                    </button>
                </form>

            @endif

        </div>

    </div>


</div>
@endsection
