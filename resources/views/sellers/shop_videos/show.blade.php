@extends('layouts.seller')

@php
    use App\Models\ShopVideoDraft;
@endphp

@section('content')

<style>

    /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    */

    .video-workflow {

        display: flex;

        align-items: flex-start;

        justify-content: space-between;

        margin-bottom: 30px;

        overflow-x: auto;

    }

    .video-workflow-step {

        flex: 1;

        min-width: 95px;

        text-align: center;

        position: relative;

    }

    .video-workflow-step:not(:last-child)::after {

        content: '';

        position: absolute;

        top: 20px;

        left: 60%;

        width: 80%;

        height: 3px;

        background: #e5e5e5;

        z-index: 1;

    }

    .video-workflow-step.completed:not(:last-child)::after {

        background: #5cb85c;

    }

    .video-workflow-icon {

        width: 42px;

        height: 42px;

        line-height: 42px;

        margin: 0 auto 8px;

        border-radius: 50%;

        background: #e5e5e5;

        color: #777;

        font-weight: bold;

        position: relative;

        z-index: 2;

    }

    .video-workflow-step.completed
    .video-workflow-icon {

        background: #5cb85c;

        color: #fff;

    }

    .video-workflow-step.processing
    .video-workflow-icon {

        background: #f0ad4e;

        color: #fff;

    }

    .video-workflow-step.error
    .video-workflow-icon {

        background: #d9534f;

        color: #fff;

    }

    .video-workflow-step.current
    .video-workflow-icon {

        box-shadow:
            0 0 0 4px
            rgba(91, 192, 222, .2);

    }

    .video-workflow-title {

        font-weight: bold;

        font-size: 13px;

    }

    .video-workflow-status {

        font-size: 11px;

        color: #777;

        margin-top: 3px;

    }


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    .video-preview {

        width: 100%;

        border-radius: 6px;

        background: #000;

    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    .status-row {

        padding: 8px 0;

        border-bottom: 1px solid #eee;

    }

    .status-row:last-child {

        border-bottom: none;

    }

    .status-label-title {

        font-weight: bold;

    }


    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    .shop-video-show {

        padding-bottom: 120px;

    }

</style>

<div class="container-fluid py-4 shop-video-show">


{{-- ============================================================= --}}
{{-- Header --}}
{{-- ============================================================= --}}

<div class="row mb-4">

    <div class="col-md-8">

        <h1 class="h3 mb-1">

            {{ $video->title }}

        </h1>

        <p class="text-muted mb-0">

            Shop動画の詳細

        </p>

    </div>


    <div class="col-md-4 text-right">

        <a
            href="{{ route('seller.shop-videos.index') }}"
            class="btn btn-default"
        >

            一覧へ戻る

        </a>

    </div>

</div>



{{-- ============================================================= --}}
{{-- Workflow --}}
{{-- ============================================================= --}}

<div class="panel panel-bordered">

    <div class="panel-heading">

        <strong>
            制作ステップ
        </strong>

    </div>


    <div class="panel-body">

        <div class="video-workflow">


            {{-- ================================================= --}}
            {{-- ① Upload --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step
                {{ $video->original_movie
                    ? 'completed'
                    : 'current' }}"
            >

                <div class="video-workflow-icon">

                    @if($video->original_movie)

                        ✓

                    @else

                        1

                    @endif

                </div>

                <div class="video-workflow-title">
                    Upload
                </div>

                <div class="video-workflow-status">

                    {{ $video->original_movie
                        ? '完了'
                        : '待機中' }}

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ② AI --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step

                @if(
                    $video->ai_status
                    === ShopVideoDraft::AI_APPROVED
                )
                    completed
                @elseif(
                    $video->ai_status
                    === ShopVideoDraft::AI_PROCESSING
                )
                    processing
                @elseif(
                    in_array(
                        $video->ai_status,
                        [
                            ShopVideoDraft::AI_WARNING,
                            ShopVideoDraft::AI_REJECTED,
                            ShopVideoDraft::AI_FAILED,
                        ],
                        true
                    )
                )
                    error
                @endif"
            >

                <div class="video-workflow-icon">

                    @if(
                        $video->ai_status
                        === ShopVideoDraft::AI_APPROVED
                    )

                        ✓

                    @elseif(
                        in_array(
                            $video->ai_status,
                            [
                                ShopVideoDraft::AI_WARNING,
                                ShopVideoDraft::AI_REJECTED,
                                ShopVideoDraft::AI_FAILED,
                            ],
                            true
                        )
                    )

                        !

                    @else

                        2

                    @endif

                </div>

                <div class="video-workflow-title">
                    AI審査
                </div>

                <div class="video-workflow-status">

                    @switch($video->ai_status)

                        @case(ShopVideoDraft::AI_APPROVED)
                            合格
                            @break

                        @case(ShopVideoDraft::AI_PROCESSING)
                            審査中
                            @break

                        @case(ShopVideoDraft::AI_WARNING)
                            確認必要
                            @break

                        @case(ShopVideoDraft::AI_REJECTED)
                            不承認
                            @break

                        @case(ShopVideoDraft::AI_FAILED)
                            エラー
                            @break

                        @default
                            待機中

                    @endswitch

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ③ Processing --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step

                @if(
                    $video->process_status
                    === ShopVideoDraft::PROCESS_COMPLETED
                )
                    completed

                @elseif(
                    $video->process_status
                    === ShopVideoDraft::PROCESS_RUNNING
                )
                    processing

                @elseif(
                    $video->process_status
                    === ShopVideoDraft::PROCESS_FAILED
                )
                    error

                @endif"
            >

                <div class="video-workflow-icon">

                    @if(
                        $video->process_status
                        === ShopVideoDraft::PROCESS_COMPLETED
                    )

                        ✓

                    @elseif(
                        $video->process_status
                        === ShopVideoDraft::PROCESS_FAILED
                    )

                        !

                    @else

                        3

                    @endif

                </div>

                <div class="video-workflow-title">
                    動画加工
                </div>

                <div class="video-workflow-status">

                    @switch($video->process_status)

                        @case(ShopVideoDraft::PROCESS_COMPLETED)
                            完了
                            @break

                        @case(ShopVideoDraft::PROCESS_RUNNING)
                            加工中
                            @break

                        @case(ShopVideoDraft::PROCESS_FAILED)
                            エラー
                            @break

                        @default
                            待機中

                    @endswitch

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ④ Preview --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step

                @if(
                    $video->preview_status
                    === ShopVideoDraft::PREVIEW_COMPLETED
                )
                    completed

                @elseif(
                    $video->preview_status
                    === ShopVideoDraft::PREVIEW_GENERATING
                )
                    processing

                @elseif(
                    $video->preview_status
                    === ShopVideoDraft::PREVIEW_FAILED
                )
                    error

                @endif"
            >

                <div class="video-workflow-icon">

                    @if(
                        $video->preview_status
                        === ShopVideoDraft::PREVIEW_COMPLETED
                    )

                        ✓

                    @elseif(
                        $video->preview_status
                        === ShopVideoDraft::PREVIEW_FAILED
                    )

                        !

                    @else

                        4

                    @endif

                </div>

                <div class="video-workflow-title">
                    Preview
                </div>

                <div class="video-workflow-status">

                    @switch($video->preview_status)

                        @case(ShopVideoDraft::PREVIEW_COMPLETED)
                            確認可能
                            @break

                        @case(ShopVideoDraft::PREVIEW_GENERATING)
                            生成中
                            @break

                        @case(ShopVideoDraft::PREVIEW_FAILED)
                            エラー
                            @break

                        @default
                            待機中

                    @endswitch

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ⑤ Seller Review --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step

                @if(
                    $video->seller_review_status
                    === ShopVideoDraft::SELLER_REVIEW_APPROVED
                )
                    completed

                @elseif(
                    $video->seller_review_status
                    === ShopVideoDraft::SELLER_REVIEW_PENDING
                )
                    processing
                @endif"
            >

                <div class="video-workflow-icon">

                    @if(
                        $video->seller_review_status
                        === ShopVideoDraft::SELLER_REVIEW_APPROVED
                    )

                        ✓

                    @else

                        5

                    @endif

                </div>

                <div class="video-workflow-title">
                    出品者確認
                </div>

                <div class="video-workflow-status">

                    @switch($video->seller_review_status)

                        @case(ShopVideoDraft::SELLER_REVIEW_APPROVED)
                            承認済み
                            @break

                        @case(ShopVideoDraft::SELLER_REVIEW_PENDING)
                            確認待ち
                            @break

                        @default
                            待機中

                    @endswitch

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ⑥ 管理者審査 --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step

                @if(
                    $video->review_status
                    === ShopVideoDraft::REVIEW_APPROVED
                )
                    completed

                @elseif(
                    $video->review_status
                    === ShopVideoDraft::REVIEW_PENDING
                )
                    processing

                @elseif(
                    $video->review_status
                    === ShopVideoDraft::REVIEW_REJECTED
                )
                    error

                @endif"
            >

                <div class="video-workflow-icon">

                    @if(
                        $video->review_status
                        === ShopVideoDraft::REVIEW_APPROVED
                    )

                        ✓

                    @elseif(
                        $video->review_status
                        === ShopVideoDraft::REVIEW_REJECTED
                    )

                        !

                    @else

                        6

                    @endif

                </div>

                <div class="video-workflow-title">
                    管理者審査
                </div>

                <div class="video-workflow-status">

                    @switch($video->review_status)

                        @case(ShopVideoDraft::REVIEW_APPROVED)
                            承認済み
                            @break

                        @case(ShopVideoDraft::REVIEW_PENDING)
                            審査待ち
                            @break

                        @case(ShopVideoDraft::REVIEW_REJECTED)
                            不承認
                            @break

                        @default
                            待機中

                    @endswitch

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ⑦ YouTube --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step

                @if(
                    in_array(
                        $video->youtube_status,
                        [
                            ShopVideoDraft::YOUTUBE_PRIVATE,
                            ShopVideoDraft::YOUTUBE_PUBLIC,
                        ],
                        true
                    )
                )
                    completed

                @elseif(
                    $video->youtube_status
                    === ShopVideoDraft::YOUTUBE_UPLOADING
                )
                    processing

                @elseif(
                    $video->youtube_status
                    === ShopVideoDraft::YOUTUBE_FAILED
                )
                    error

                @endif"
            >

                <div class="video-workflow-icon">

                    @if(
                        in_array(
                            $video->youtube_status,
                            [
                                ShopVideoDraft::YOUTUBE_PRIVATE,
                                ShopVideoDraft::YOUTUBE_PUBLIC,
                            ],
                            true
                        )
                    )

                        ✓

                    @elseif(
                        $video->youtube_status
                        === ShopVideoDraft::YOUTUBE_FAILED
                    )

                        !

                    @else

                        7

                    @endif

                </div>

                <div class="video-workflow-title">
                    YouTube
                </div>

                <div class="video-workflow-status">

                    @switch($video->youtube_status)

                        @case(ShopVideoDraft::YOUTUBE_UPLOADING)
                            投稿中
                            @break

                        @case(ShopVideoDraft::YOUTUBE_PRIVATE)
                            限定公開
                            @break

                        @case(ShopVideoDraft::YOUTUBE_PUBLIC)
                            公開
                            @break

                        @case(ShopVideoDraft::YOUTUBE_FAILED)
                            エラー
                            @break

                        @default
                            未投稿

                    @endswitch

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ⑧ SNS --}}
            {{-- ================================================= --}}

            @php

                $snsCompleted =
                    in_array(
                        $video->instagram_status,
                        [
                            ShopVideoDraft::SNS_PRIVATE,
                            ShopVideoDraft::SNS_PUBLIC,
                        ],
                        true
                    )
                    ||
                    in_array(
                        $video->tiktok_status,
                        [
                            ShopVideoDraft::SNS_PRIVATE,
                            ShopVideoDraft::SNS_PUBLIC,
                        ],
                        true
                    )
                    ||
                    in_array(
                        $video->facebook_status,
                        [
                            ShopVideoDraft::SNS_PRIVATE,
                            ShopVideoDraft::SNS_PUBLIC,
                        ],
                        true
                    )
                    ||
                    in_array(
                        $video->x_status,
                        [
                            ShopVideoDraft::SNS_PRIVATE,
                            ShopVideoDraft::SNS_PUBLIC,
                        ],
                        true
                    );

            @endphp

            <div
                class="video-workflow-step
                {{ $snsCompleted ? 'completed' : '' }}"
            >

                <div class="video-workflow-icon">

                    {{ $snsCompleted ? '✓' : '8' }}

                </div>

                <div class="video-workflow-title">
                    SNS
                </div>

                <div class="video-workflow-status">

                    {{ $snsCompleted
                        ? '投稿済み'
                        : '投稿管理' }}

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- ⑨ Shop --}}
            {{-- ================================================= --}}

            <div
                class="video-workflow-step

                @if(
                    $video->publish_status
                    === ShopVideoDraft::PUBLISH_PUBLISHED
                )
                    completed
                @endif"
            >

                <div class="video-workflow-icon">

                    @if(
                        $video->publish_status
                        === ShopVideoDraft::PUBLISH_PUBLISHED
                    )

                        ✓

                    @else

                        9

                    @endif

                </div>

                <div class="video-workflow-title">
                    Shop公開
                </div>

                <div class="video-workflow-status">

                    @if(
                        $video->publish_status
                        === ShopVideoDraft::PUBLISH_PUBLISHED
                    )

                        公開済み

                    @else

                        下書き

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>



{{-- ============================================================= --}}
{{-- Main --}}
{{-- ============================================================= --}}

<div class="row">


    {{-- ========================================================= --}}
    {{-- 左 --}}
    {{-- ========================================================= --}}

    <div class="col-md-7">


        <div class="panel panel-bordered">

            <div class="panel-heading">

                <strong>
                    Preview動画
                </strong>

            </div>

            <div class="panel-body">

                @if($video->preview_movie)

                    <video
                        controls
                        playsinline
                        class="video-preview"
                    >
                        <source
                            src="{{ Storage::disk('s3')->temporaryUrl(
                                $video->preview_movie,
                                now()->addMinutes(30)
                            ) }}"
                            type="{{ $video->mime_type ?? 'video/mp4' }}"
                        >
                    </video>

                @else

                    <div class="alert alert-info mb-0">

                        加工後動画はまだ生成されていません。

                    </div>

                @endif

            </div>

        </div>



        @if($video->description)

            <div class="panel panel-bordered">

                <div class="panel-heading">

                    <strong>
                        動画説明
                    </strong>

                </div>

                <div class="panel-body">

                    {!! nl2br(e($video->description)) !!}

                </div>

            </div>

        @endif


    </div>



    {{-- ========================================================= --}}
    {{-- 右 --}}
    {{-- ========================================================= --}}

    <div class="col-md-5">


        {{-- ===================================================== --}}
        {{-- Thumbnail --}}
        {{-- ===================================================== --}}

        <div class="panel panel-bordered">

            <div class="panel-heading">

                <strong>
                    サムネイル
                </strong>

            </div>

            <div class="panel-body">

                @if($video->thumbnail)

                    <img
                        src="{{ Storage::disk('s3')->temporaryUrl(
                            $video->thumbnail,
                            now()->addMinutes(30)
                        ) }}"
                        class="img-responsive"
                        style="width:100%; border-radius:6px;"
                        alt="{{ $video->title }}"
                    >

                @else

                    <div class="alert alert-secondary mb-0">

                        サムネイルはまだ生成されていません。

                    </div>

                @endif

            </div>

        </div>



        {{-- ===================================================== --}}
        {{-- 制作状況 --}}
        {{-- ===================================================== --}}

        <div class="panel panel-bordered">

            <div class="panel-heading">

                <strong>
                    制作状況
                </strong>

            </div>

            <div class="panel-body">


                <div class="status-row">

                    <span class="status-label-title">
                        AI審査
                    </span>

                    <span class="pull-right">
                        {{ $video->ai_status ?? '-' }}
                    </span>

                </div>


                <div class="status-row">

                    <span class="status-label-title">
                        動画加工
                    </span>

                    <span class="pull-right">
                        {{ $video->process_status ?? '-' }}
                    </span>

                </div>


                <div class="status-row">

                    <span class="status-label-title">
                        Preview
                    </span>

                    <span class="pull-right">
                        {{ $video->preview_status ?? '-' }}
                    </span>

                </div>


                <div class="status-row">

                    <span class="status-label-title">
                        出品者確認
                    </span>

                    <span class="pull-right">
                        {{ $video->seller_review_status ?? '-' }}
                    </span>

                </div>


                <div class="status-row">

                    <span class="status-label-title">
                        管理者審査
                    </span>

                    <span class="pull-right">
                        {{ $video->review_status ?? '-' }}
                    </span>

                </div>


                <div class="status-row">

                    <span class="status-label-title">
                        YouTube
                    </span>

                    <span class="pull-right">
                        {{ $video->youtube_status ?? '-' }}
                    </span>

                </div>


                <div class="status-row">

                    <span class="status-label-title">
                        Shop
                    </span>

                    <span class="pull-right">
                        {{ $video->publish_status ?? '-' }}
                    </span>

                </div>


                <div class="status-row">

                    <span class="status-label-title">
                        Workflow
                    </span>

                    <span class="pull-right">
                        {{ $video->workflow_stage ?? '-' }}
                    </span>

                </div>


            </div>

        </div>



        {{-- ===================================================== --}}
        {{-- 次の操作 --}}
        {{-- ===================================================== --}}

        <div class="panel panel-bordered">

            <div class="panel-heading">

                <strong>
                    次の操作
                </strong>

            </div>

            <div class="panel-body">


                {{-- ============================================= --}}
                {{-- AI Warning --}}
                {{-- ============================================= --}}

                @if(
                    $video->ai_status
                    === ShopVideoDraft::AI_WARNING
                )

                    <div class="alert alert-warning">

                        AI審査で確認が必要です。

                    </div>


                {{-- ============================================= --}}
                {{-- AI Rejected --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->ai_status
                    === ShopVideoDraft::AI_REJECTED
                )

                    <div class="alert alert-danger">

                        AI審査で不承認となっています。

                    </div>


                {{-- ============================================= --}}
                {{-- AI Processing --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->ai_status
                    === ShopVideoDraft::AI_PROCESSING
                )

                    <div class="alert alert-info">

                        AI審査を実行しています。

                    </div>


                {{-- ============================================= --}}
                {{-- AI Approved / Edit --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->ai_status
                    === ShopVideoDraft::AI_APPROVED
                    &&
                    $video->process_status
                    === ShopVideoDraft::PROCESS_WAITING
                    &&
                    $video->edit_mode
                    === ShopVideoDraft::EDIT_MODE_PENDING
                )

                    <div class="alert alert-info">

                        AI審査に合格しました。

                        <br>

                        動画の加工方法を選択してください。

                    </div>

                    <a
                        href="{{ route(
                            'seller.shop-videos.edit',
                            ['shopVideo' => $video->id]
                        ) }}"
                        class="btn btn-primary btn-block"
                    >

                        動画加工を行う

                    </a>


                {{-- ============================================= --}}
                {{-- Processing --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->process_status
                    === ShopVideoDraft::PROCESS_RUNNING
                )

                    <div class="alert alert-info">

                        動画を加工しています。

                        <br>

                        完了するまでしばらくお待ちください。

                    </div>


                {{-- ============================================= --}}
                {{-- Processing Failed --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->process_status
                    === ShopVideoDraft::PROCESS_FAILED
                )

                    <div class="alert alert-danger">

                        動画加工に失敗しました。

                        <br>

                        管理者へお問い合わせください。

                    </div>


                {{-- ============================================= --}}
                {{-- Seller Review Pending --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->process_status
                    === ShopVideoDraft::PROCESS_COMPLETED
                    &&
                    $video->seller_review_status
                    === ShopVideoDraft::SELLER_REVIEW_PENDING
                )

                    <div class="alert alert-warning">

                        <strong>
                            出品者による確認が必要です。
                        </strong>

                        <br><br>

                        加工後の動画を確認し、
                        問題がなければ承認してください。

                    </div>

                    <a
                        href="{{ route(
                            'seller.shop-videos.edit',
                            ['shopVideo' => $video->id]
                        ) }}"
                        class="btn btn-primary btn-block"
                    >

                        加工内容を確認・承認する

                    </a>


                {{-- ============================================= --}}
                {{-- Seller Review Approved --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->seller_review_status
                    === ShopVideoDraft::SELLER_REVIEW_APPROVED
                    &&
                    $video->review_status
                    === ShopVideoDraft::REVIEW_PENDING
                )

                    <div class="alert alert-info">

                        出品者確認が完了しました。

                        <br>

                        管理者審査待ちです。

                    </div>


                {{-- ============================================= --}}
                {{-- Admin Rejected --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->review_status
                    === ShopVideoDraft::REVIEW_REJECTED
                )

                    <div class="alert alert-danger">

                        <strong>
                            管理者審査で不承認となっています。
                        </strong>

                        <br><br>

                        この動画は公開できません。

                    </div>


                {{-- ============================================= --}}
                {{-- YouTube --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->youtube_status
                    === ShopVideoDraft::YOUTUBE_UPLOADING
                )

                    <div class="alert alert-info">

                        YouTubeへアップロードしています。

                    </div>


                @elseif(
                    $video->youtube_status
                    === ShopVideoDraft::YOUTUBE_PRIVATE
                )

                    <div class="alert alert-info">

                        YouTubeへ限定公開されています。

                    </div>


                {{-- ============================================= --}}
                {{-- Published --}}
                {{-- ============================================= --}}

                @elseif(
                    $video->publish_status
                    === ShopVideoDraft::PUBLISH_PUBLISHED
                )

                    <div class="alert alert-success">

                        Shop動画として公開されています。

                    </div>


                {{-- ============================================= --}}
                {{-- Default --}}
                {{-- ============================================= --}}

                @else

                    <div class="alert alert-secondary">

                        次の処理を準備しています。

                    </div>

                @endif


            </div>

        </div>


    </div>

</div>

</div>

@endsection