@extends('layouts.seller')

@section('content')

<style>

    /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    */

    .workflow-overview {

        display: flex;

        align-items: center;

        justify-content: space-between;

        margin-bottom: 25px;

        padding: 15px 20px;

        background: #f8f8f8;

        border: 1px solid #e5e5e5;

        border-radius: 6px;

    }

    .workflow-overview-step {

        display: flex;

        align-items: center;

        white-space: nowrap;

        font-size: 12px;

    }

    .workflow-overview-step .step {

        padding: 7px 12px;

        border-radius: 20px;

        background: #e5e5e5;

        color: #777;

    }

    .workflow-overview-step .step.active {

        background: #337ab7;

        color: #fff;

    }

    .workflow-overview-step .step.completed {

        background: #5cb85c;

        color: #fff;

    }

    .workflow-overview-arrow {

        margin: 0 8px;

        color: #aaa;

    }


    /*
    |--------------------------------------------------------------------------
    | Video Card
    |--------------------------------------------------------------------------
    */

    .video-card {

        height: 100%;

    }

    .video-thumbnail {

        width: 100%;

        height: 180px;

        object-fit: cover;

        border-radius: 6px;

    }

    .video-thumbnail-placeholder {

        width: 100%;

        height: 180px;

        background: #ececec;

        border-radius: 6px;

        display: flex;

        justify-content: center;

        align-items: center;

        font-size: 22px;

        color: #999;

    }


    /*
    |--------------------------------------------------------------------------
    | Current Stage
    |--------------------------------------------------------------------------
    */

    .current-stage {

        margin-top: 15px;

        margin-bottom: 15px;

        padding: 10px 12px;

        border-radius: 5px;

        background: #f5f5f5;

        border-left: 4px solid #777;

    }

    .current-stage.processing {

        background: #fcf8e3;

        border-left-color: #f0ad4e;

    }

    .current-stage.completed {

        background: #dff0d8;

        border-left-color: #5cb85c;

    }

    .current-stage.warning {

        background: #fcf8e3;

        border-left-color: #f0ad4e;

    }

    .current-stage.error {

        background: #f2dede;

        border-left-color: #d9534f;

    }

    .current-stage-label {

        font-size: 11px;

        color: #777;

        margin-bottom: 3px;

    }

    .current-stage-name {

        font-weight: bold;

        font-size: 15px;

    }


    /*
    |--------------------------------------------------------------------------
    | Video Workflow
    |--------------------------------------------------------------------------
    */

    .video-workflow {

        margin-top: 15px;

        margin-bottom: 15px;

    }

    .video-workflow-row {

        display: flex;

        align-items: center;

        margin-bottom: 7px;

        font-size: 12px;

    }

    .video-workflow-icon {

        width: 22px;

        height: 22px;

        margin-right: 8px;

        border-radius: 50%;

        display: flex;

        justify-content: center;

        align-items: center;

        background: #e5e5e5;

        color: #777;

        font-size: 11px;

    }

    .video-workflow-row.completed
    .video-workflow-icon {

        background: #5cb85c;

        color: #fff;

    }

    .video-workflow-row.processing
    .video-workflow-icon {

        background: #f0ad4e;

        color: #fff;

    }

    .video-workflow-row.error
    .video-workflow-icon {

        background: #d9534f;

        color: #fff;

    }

    .video-workflow-name {

        flex: 1;

    }

    .video-workflow-status {

        color: #999;

    }


    /*
    |--------------------------------------------------------------------------
    | Status Labels
    |--------------------------------------------------------------------------
    */

    .status-label {

        margin-right: 4px;

        margin-bottom: 4px;

        display: inline-block;

    }


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    .summary-number {

        font-size: 28px;

        font-weight: bold;

        margin-bottom: 3px;

    }

    .summary-label {

        color: #777;

    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 768px) {

        .workflow-overview {

            overflow-x: auto;

            justify-content: flex-start;

        }

        .workflow-overview-step {

            flex-shrink: 0;

        }

        .workflow-overview-arrow {

            flex-shrink: 0;

        }

    }

</style>


<div class="container-fluid">


    {{-- ============================================================= --}}
    {{-- Header --}}
    {{-- ============================================================= --}}

    <div class="row">

        <div class="col-md-8">

            <h2>
                Shop動画管理
            </h2>

            <p class="text-muted">

                店舗紹介動画の制作・審査・公開状況を確認できます。

            </p>

        </div>


        <div class="col-md-4 text-right">

            <a

                href="{{ route('seller.shop-videos.create') }}"

                class="btn btn-success"

            >

                <i class="voyager-plus"></i>

                新しい動画

            </a>

        </div>

    </div>


    <br>


    {{-- ============================================================= --}}
    {{-- Overall Workflow --}}
    {{-- ============================================================= --}}

    <div class="workflow-overview">


        <div class="workflow-overview-step">

            <span class="step completed">

                Upload

            </span>

        </div>


        <span class="workflow-overview-arrow">
            →
        </span>


        <div class="workflow-overview-step">

            <span class="step">

                Preview

            </span>

        </div>


        <span class="workflow-overview-arrow">
            →
        </span>


        <div class="workflow-overview-step">

            <span class="step">

                AI審査

            </span>

        </div>


        <span class="workflow-overview-arrow">
            →
        </span>


        <div class="workflow-overview-step">

            <span class="step">

                動画加工

            </span>

        </div>


        <span class="workflow-overview-arrow">
            →
        </span>


        <div class="workflow-overview-step">

            <span class="step">

                確認

            </span>

        </div>


        <span class="workflow-overview-arrow">
            →
        </span>


        <div class="workflow-overview-step">

            <span class="step">

                SNS

            </span>

        </div>


        <span class="workflow-overview-arrow">
            →
        </span>


        <div class="workflow-overview-step">

            <span class="step">

                公開

            </span>

        </div>


    </div>


    {{-- ============================================================= --}}
    {{-- Summary --}}
    {{-- ============================================================= --}}

    <div class="row">


        {{-- Total --}}

        <div class="col-md-2 col-sm-4">

            <div class="panel panel-bordered">

                <div class="panel-body text-center">

                    <div class="summary-number">

                        {{ $summary['total'] }}

                    </div>

                    <div class="summary-label">

                        動画数

                    </div>

                </div>

            </div>

        </div>


        {{-- Preview --}}

        <div class="col-md-2 col-sm-4">

            <div class="panel panel-bordered">

                <div class="panel-body text-center">

                    <div class="summary-number">

                        {{ $videos->where(
                            'preview_status',
                            \App\Models\ShopVideoDraft::PREVIEW_WAITING
                        )->count() }}

                    </div>

                    <div class="summary-label">

                        Preview待ち

                    </div>

                </div>

            </div>

        </div>


        {{-- AI --}}

        <div class="col-md-2 col-sm-4">

            <div class="panel panel-bordered">

                <div class="panel-body text-center">

                    <div class="summary-number">

                        {{ $summary['ai_pending'] }}

                    </div>

                    <div class="summary-label">

                        AI審査中

                    </div>

                </div>

            </div>

        </div>


        {{-- Processing --}}

        <div class="col-md-2 col-sm-4">

            <div class="panel panel-bordered">

                <div class="panel-body text-center">

                    <div class="summary-number">

                        {{ $summary['processing'] }}

                    </div>

                    <div class="summary-label">

                        加工中

                    </div>

                </div>

            </div>

        </div>


        {{-- Warning --}}

        <div class="col-md-2 col-sm-4">

            <div class="panel panel-bordered">

                <div class="panel-body text-center">

                    <div class="summary-number">

                        {{ $summary['warning'] }}

                    </div>

                    <div class="summary-label">

                        AI要確認

                    </div>

                </div>

            </div>

        </div>


        {{-- Published --}}

        <div class="col-md-2 col-sm-4">

            <div class="panel panel-bordered">

                <div class="panel-body text-center">

                    <div class="summary-number">

                        {{ $summary['published'] }}

                    </div>

                    <div class="summary-label">

                        公開済み

                    </div>

                </div>

            </div>

        </div>


    </div>


    <br>


    {{-- ============================================================= --}}
    {{-- Video List --}}
    {{-- ============================================================= --}}

    <div class="row">


        @forelse($videos as $video)


            @php

                /*
                |--------------------------------------------------------------------------
                | Current Stage
                |--------------------------------------------------------------------------
                */

                $stage = $video->workflow_stage;


                $stageLabels = [

                    \App\Models\ShopVideoDraft::STAGE_UPLOAD
                        => 'アップロード',

                    \App\Models\ShopVideoDraft::STAGE_PREVIEW
                        => 'Preview生成',

                    \App\Models\ShopVideoDraft::STAGE_AI
                        => 'AI審査',

                    \App\Models\ShopVideoDraft::STAGE_PROCESS
                        => '動画加工',

                    \App\Models\ShopVideoDraft::STAGE_REVIEW
                        => '管理者確認',

                    \App\Models\ShopVideoDraft::STAGE_SNS
                        => 'SNS',

                    \App\Models\ShopVideoDraft::STAGE_YOUTUBE
                        => 'YouTube',

                    \App\Models\ShopVideoDraft::STAGE_INSTAGRAM
                        => 'Instagram',

                    \App\Models\ShopVideoDraft::STAGE_PUBLISHED
                        => '公開',

                    \App\Models\ShopVideoDraft::STAGE_COMPLETED
                        => '完了',

                ];


                $currentStage =
                    $stageLabels[$stage]
                    ?? '処理中';


                /*
                |--------------------------------------------------------------------------
                | Current Stage Class
                |--------------------------------------------------------------------------
                */

                $stageClass =
                    'processing';


                if (
                    $stage
                    === \App\Models\ShopVideoDraft::STAGE_COMPLETED
                    ||
                    $video->publish_status === 'published'
                ) {

                    $stageClass =
                        'completed';

                }


                if (
                    $video->ai_status
                    === \App\Models\ShopVideoDraft::AI_WARNING
                    ||
                    $video->review_status
                    === \App\Models\ShopVideoDraft::REVIEW_PENDING
                    &&
                    $stage
                    === \App\Models\ShopVideoDraft::STAGE_REVIEW
                ) {

                    $stageClass =
                        'warning';

                }


                if (
                    $video->ai_status
                    === \App\Models\ShopVideoDraft::AI_REJECTED
                    ||
                    $video->process_status
                    === \App\Models\ShopVideoDraft::PROCESS_FAILED
                    ||
                    $video->preview_status
                    === \App\Models\ShopVideoDraft::PREVIEW_FAILED
                ) {

                    $stageClass =
                        'error';

                }


                /*
                |--------------------------------------------------------------------------
                | Workflow Steps
                |--------------------------------------------------------------------------
                */

                $workflowSteps = [

                    [
                        'key' => 'upload',
                        'label' => 'Upload',
                    ],

                    [
                        'key' => 'preview',
                        'label' => 'Preview',
                    ],

                    [
                        'key' => 'ai',
                        'label' => 'AI審査',
                    ],

                    [
                        'key' => 'process',
                        'label' => '動画加工',
                    ],

                    [
                        'key' => 'review',
                        'label' => '確認',
                    ],

                    [
                        'key' => 'sns',
                        'label' => 'SNS',
                    ],

                    [
                        'key' => 'published',
                        'label' => '公開',
                    ],

                ];


                /*
                |--------------------------------------------------------------------------
                | Workflow order
                |--------------------------------------------------------------------------
                */

                $workflowOrder = [

                    'upload' => 1,

                    'preview' => 2,

                    'ai' => 3,

                    'process' => 4,

                    'review' => 5,

                    'sns' => 6,

                    'youtube' => 6,

                    'instagram' => 6,

                    'published' => 7,

                    'completed' => 7,

                ];


                $currentOrder =
                    $workflowOrder[$stage]
                    ?? 1;

            @endphp


            <div class="col-md-4 col-lg-3">


                <div class="panel panel-bordered video-card">


                    {{-- ================================================= --}}
                    {{-- Title --}}
                    {{-- ================================================= --}}

                    <div class="panel-heading">

                        <strong>

                            {{ $video->title }}

                        </strong>

                    </div>


                    <div class="panel-body">


                        {{-- ================================================= --}}
                        {{-- Thumbnail --}}
                        {{-- ================================================= --}}

                        @if($video->thumbnail)

                            <img

                                src="{{ Storage::disk('s3')->temporaryUrl(

                                    $video->thumbnail,

                                    now()->addMinutes(30)

                                ) }}"

                                class="video-thumbnail"

                                alt="{{ $video->title }}"

                            >

                        @else

                            <div class="video-thumbnail-placeholder">

                                🎬

                            </div>

                        @endif


                        {{-- ================================================= --}}
                        {{-- Current Stage --}}
                        {{-- ================================================= --}}

                        <div class="current-stage {{ $stageClass }}">


                            <div class="current-stage-label">

                                現在の工程

                            </div>


                            <div class="current-stage-name">

                                @if($stageClass === 'completed')

                                    ✓

                                @elseif($stageClass === 'error')

                                    !

                                @elseif($stageClass === 'warning')

                                    !

                                @else

                                    ●

                                @endif


                                {{ $currentStage }}

                            </div>


                        </div>


                        {{-- ================================================= --}}
                        {{-- Workflow --}}
                        {{-- ================================================= --}}

                        <div class="video-workflow">


                            @foreach($workflowSteps as $index => $step)


                                @php

                                    $stepOrder =
                                        $index + 1;


                                    $stepClass = '';


                                    $stepStatus =
                                        '待機';


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Upload
                                    |--------------------------------------------------------------------------
                                    */

                                    if (
                                        $step['key'] === 'upload'
                                    ) {

                                        if (
                                            $video->original_movie
                                        ) {

                                            $stepClass =
                                                'completed';

                                            $stepStatus =
                                                '完了';

                                        }

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Preview
                                    |--------------------------------------------------------------------------
                                    */

                                    elseif (
                                        $step['key'] === 'preview'
                                    ) {

                                        if (
                                            $video->preview_status
                                            === \App\Models\ShopVideoDraft::PREVIEW_COMPLETED
                                        ) {

                                            $stepClass =
                                                'completed';

                                            $stepStatus =
                                                '完了';

                                        }

                                        elseif (
                                            $video->preview_status
                                            === \App\Models\ShopVideoDraft::PREVIEW_GENERATING
                                        ) {

                                            $stepClass =
                                                'processing';

                                            $stepStatus =
                                                '生成中';

                                        }

                                        elseif (
                                            $video->preview_status
                                            === \App\Models\ShopVideoDraft::PREVIEW_FAILED
                                        ) {

                                            $stepClass =
                                                'error';

                                            $stepStatus =
                                                '失敗';

                                        }

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | AI
                                    |--------------------------------------------------------------------------
                                    */

                                    elseif (
                                        $step['key'] === 'ai'
                                    ) {

                                        if (
                                            $video->ai_status
                                            === \App\Models\ShopVideoDraft::AI_APPROVED
                                        ) {

                                            $stepClass =
                                                'completed';

                                            $stepStatus =
                                                '合格';

                                        }

                                        elseif (
                                            $video->ai_status
                                            === \App\Models\ShopVideoDraft::AI_WARNING
                                        ) {

                                            $stepClass =
                                                'processing';

                                            $stepStatus =
                                                '要確認';

                                        }

                                        elseif (
                                            $video->ai_status
                                            === \App\Models\ShopVideoDraft::AI_REJECTED
                                        ) {

                                            $stepClass =
                                                'error';

                                            $stepStatus =
                                                '不承認';

                                        }

                                        elseif (
                                            $video->ai_status
                                            === \App\Models\ShopVideoDraft::AI_PROCESSING
                                        ) {

                                            $stepClass =
                                                'processing';

                                            $stepStatus =
                                                '審査中';

                                        }

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Process
                                    |--------------------------------------------------------------------------
                                    */

                                    elseif (
                                        $step['key'] === 'process'
                                    ) {

                                        if (
                                            $video->process_status
                                            === \App\Models\ShopVideoDraft::PROCESS_COMPLETED
                                        ) {

                                            $stepClass =
                                                'completed';

                                            $stepStatus =
                                                '完了';

                                        }

                                        elseif (
                                            $video->process_status
                                            === \App\Models\ShopVideoDraft::PROCESS_RUNNING
                                        ) {

                                            $stepClass =
                                                'processing';

                                            $stepStatus =
                                                '加工中';

                                        }

                                        elseif (
                                            $video->process_status
                                            === \App\Models\ShopVideoDraft::PROCESS_FAILED
                                        ) {

                                            $stepClass =
                                                'error';

                                            $stepStatus =
                                                '失敗';

                                        }

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Review
                                    |--------------------------------------------------------------------------
                                    */

                                    elseif (
                                        $step['key'] === 'review'
                                    ) {

                                        if (
                                            $video->review_status
                                            === \App\Models\ShopVideoDraft::REVIEW_APPROVED
                                        ) {

                                            $stepClass =
                                                'completed';

                                            $stepStatus =
                                                '承認';

                                        }

                                        elseif (
                                            $video->review_status
                                            === \App\Models\ShopVideoDraft::REVIEW_REJECTED
                                        ) {

                                            $stepClass =
                                                'error';

                                            $stepStatus =
                                                '却下';

                                        }

                                        elseif (
                                            $stage
                                            === \App\Models\ShopVideoDraft::STAGE_REVIEW
                                        ) {

                                            $stepClass =
                                                'processing';

                                            $stepStatus =
                                                '確認待ち';

                                        }

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | SNS
                                    |--------------------------------------------------------------------------
                                    */

                                    elseif (
                                        $step['key'] === 'sns'
                                    ) {

                                        if (
                                            $video->youtube_status
                                            === \App\Models\ShopVideoDraft::YOUTUBE_PUBLIC
                                        ) {

                                            $stepClass =
                                                'completed';

                                            $stepStatus =
                                                '公開済み';

                                        }

                                        elseif (
                                            $video->youtube_status
                                            === \App\Models\ShopVideoDraft::YOUTUBE_PRIVATE
                                        ) {

                                            $stepClass =
                                                'processing';

                                            $stepStatus =
                                                '投稿済み';

                                        }

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Publish
                                    |--------------------------------------------------------------------------
                                    */

                                    elseif (
                                        $step['key'] === 'published'
                                    ) {

                                        if (
                                            $video->publish_status
                                            === 'published'
                                        ) {

                                            $stepClass =
                                                'completed';

                                            $stepStatus =
                                                '公開済み';

                                        }

                                    }

                                @endphp


                                <div
                                    class="video-workflow-row {{ $stepClass }}"
                                >


                                    <div class="video-workflow-icon">


                                        @if(
                                            $stepClass === 'completed'
                                        )

                                            ✓

                                        @elseif(
                                            $stepClass === 'processing'
                                        )

                                            ●

                                        @elseif(
                                            $stepClass === 'error'
                                        )

                                            !

                                        @else

                                            {{ $stepOrder }}

                                        @endif


                                    </div>


                                    <div class="video-workflow-name">

                                        {{ $step['label'] }}

                                    </div>


                                    <div class="video-workflow-status">

                                        {{ $stepStatus }}

                                    </div>


                                </div>


                            @endforeach


                        </div>


                        {{-- ================================================= --}}
                        {{-- Basic Info --}}
                        {{-- ================================================= --}}

                        <table class="table table-condensed">


                            <tr>

                                <th>
                                    時間
                                </th>

                                <td>

                                    {{ gmdate(

                                        'i:s',

                                        $video->duration ?? 0

                                    ) }}

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    サイズ
                                </th>

                                <td>

                                    {{ number_format(

                                        ($video->file_size ?? 0)

                                        / 1024

                                        / 1024,

                                        1

                                    ) }}

                                    MB

                                </td>

                            </tr>


                        </table>


                        {{-- ================================================= --}}
                        {{-- Important Status --}}
                        {{-- ================================================= --}}

                        @if(
                            $video->ai_status
                            === \App\Models\ShopVideoDraft::AI_WARNING
                        )

                            <span class="label label-warning status-label">

                                AI要確認

                            </span>

                        @elseif(
                            $video->ai_status
                            === \App\Models\ShopVideoDraft::AI_APPROVED
                        )

                            <span class="label label-success status-label">

                                AI OK

                            </span>

                        @elseif(
                            $video->ai_status
                            === \App\Models\ShopVideoDraft::AI_REJECTED
                        )

                            <span class="label label-danger status-label">

                                AI拒否

                            </span>

                        @endif


                        @if(
                            $video->process_status
                            === \App\Models\ShopVideoDraft::PROCESS_FAILED
                        )

                            <span class="label label-danger status-label">

                                加工失敗

                            </span>

                        @endif


                        @if(
                            $video->review_status
                            === \App\Models\ShopVideoDraft::REVIEW_PENDING
                            &&
                            $stage
                            === \App\Models\ShopVideoDraft::STAGE_REVIEW
                        )

                            <span class="label label-warning status-label">

                                管理者確認待ち

                            </span>

                        @endif


                        {{-- ================================================= --}}
                        {{-- Buttons --}}
                        {{-- ================================================= --}}

                        <br>

                        <br>


                        <div class="btn-group btn-group-justified">


                            <a

                                href="{{ route(

                                    'seller.shop-videos.show',

                                    $video

                                ) }}"

                                class="btn btn-primary"

                            >

                                詳細

                            </a>


                            @if(
                                $video->preview_movie
                            )

                                <a

                                    href="{{ Storage::disk('s3')->temporaryUrl(

                                        $video->preview_movie,

                                        now()->addMinutes(30)

                                    ) }}"

                                    target="_blank"

                                    class="btn btn-success"

                                >

                                    Preview

                                </a>

                            @else

                                <button

                                    type="button"

                                    class="btn btn-default"

                                    disabled

                                >

                                    Preview

                                </button>

                            @endif


                            <a

                                href="#"

                                class="btn btn-info"

                            >

                                履歴

                            </a>


                        </div>


                    </div>

                </div>


            </div>


        @empty


            <div class="col-md-12">

                <div class="alert alert-info">

                    動画はまだ登録されていません。

                </div>

            </div>


        @endforelse


    </div>


    {{-- ============================================================= --}}
    {{-- Pagination --}}
    {{-- ============================================================= --}}

    <div class="text-center">

        {{ $videos->links() }}

    </div>


</div>

@endsection