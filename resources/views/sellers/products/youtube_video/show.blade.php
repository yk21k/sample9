@extends('layouts.seller')

@section('content')

<div class="container">

    <div class="row">

        <div class="col-md-8 col-md-offset-2">

            <div class="panel panel-default">

                <div class="panel-heading">
                    商品SNS用動画
                </div>

                <div class="panel-body">

                    @php

                        $isVideoProcessing =
                            in_array(
                                $video->ai_status,
                                [
                                    \App\Models\ProductVideoDraft::AI_PENDING,
                                    \App\Models\ProductVideoDraft::AI_PROCESSING,
                                ],
                                true
                            )
                            ||
                            in_array(
                                $video->process_status,
                                [
                                    \App\Models\ProductVideoDraft::PROCESS_RUNNING,
                                ],
                                true
                            )
                            ||
                            in_array(
                                $video->preview_status,
                                [
                                    \App\Models\ProductVideoDraft::PREVIEW_GENERATING,
                                ],
                                true
                            );

                    @endphp

                    {{-- 成功メッセージ --}}
                    @if (session('success'))

                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>

                    @endif


                    {{-- エラーメッセージ --}}
                    @if (session('error'))

                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>

                    @endif


                    {{-- バリデーションエラー --}}
                    @if ($errors->any())

                        <div class="alert alert-danger">

                            <ul style="margin-bottom: 0;">

                                @foreach ($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- 商品情報 --}}
                    <div class="form-group">

                        <label>
                            商品
                        </label>

                        <div
                            class="form-control"
                            style="
                                height: auto;
                                background: #f8f8f8;
                            "
                        >

                            {{ $product->name }}

                        </div>

                    </div>


                    {{-- 動画タイトル --}}
                    <div class="form-group">

                        <label>
                            動画タイトル
                        </label>

                        <div
                            class="form-control"
                            style="
                                height: auto;
                                background: #f8f8f8;
                            "
                        >

                            {{ $video->title }}

                        </div>

                    </div>


                    {{-- 動画説明 --}}
                    @if ($video->description)

                        <div class="form-group">

                            <label>
                                動画説明
                            </label>

                            <div
                                class="form-control"
                                style="
                                    height: auto;
                                    min-height: 100px;
                                    background: #f8f8f8;
                                "
                            >

                                {!! nl2br(e($video->description)) !!}

                            </div>

                        </div>

                    @endif

                    
                    {{-- Preview動画 --}}
                    @if (
                        $video->preview_status
                            === \App\Models\ProductVideoDraft::PREVIEW_COMPLETED &&
                        $previewUrl
                    )

                        <hr>

                        <div class="form-group">

                            <h4>
                                Preview動画
                            </h4>

                            <div
                                style="
                                    background: #111;
                                    padding: 15px;
                                    border-radius: 4px;
                                    text-align: center;
                                "
                            >

                                <video
                                    controls
                                    playsinline
                                    preload="metadata"
                                    style="
                                        width: 100%;
                                        max-height: 500px;
                                        display: block;
                                        margin: 0 auto;
                                    "
                                >
                                    <source
                                        src="{{ $previewUrl }}"
                                        type="video/mp4"
                                    >

                                    お使いのブラウザでは
                                    動画を再生できません。
                                </video>

                            </div>

                            <p
                                class="help-block"
                                style="margin-top: 10px;"
                            >
                                このPreview動画を確認して、
                                問題がなければ出品者確認を完了してください。
                            </p>

                        </div>

                    @endif

                    {{-- ==========================================
                         Workflow進行状況
                    ========================================== --}}
                    @php

                        $workflowStages = [
                            \App\Models\ProductVideoDraft::STAGE_UPLOAD => 'Upload',
                            \App\Models\ProductVideoDraft::STAGE_AI => 'AI審査',
                            \App\Models\ProductVideoDraft::STAGE_PROCESS => '動画加工',
                            \App\Models\ProductVideoDraft::STAGE_PREVIEW => 'Preview',
                            \App\Models\ProductVideoDraft::STAGE_SELLER_REVIEW => '出品者確認',
                            \App\Models\ProductVideoDraft::STAGE_REVIEW => '管理者審査',
                            \App\Models\ProductVideoDraft::STAGE_YOUTUBE => 'YouTube',
                            \App\Models\ProductVideoDraft::STAGE_COMPLETED => '完了',
                        ];

                        $workflowStageKeys = array_keys($workflowStages);

                        $currentStageIndex =
                            array_search(
                                $video->workflow_stage,
                                $workflowStageKeys,
                                true
                            );

                    @endphp

                    <div
                        style="
                            margin-bottom: 25px;
                            padding: 20px;
                            background: #222;
                            border: 1px solid #444;
                            border-radius: 6px;
                        "
                    >

                        <h4 style="margin-top: 0;">
                            動画Workflow
                        </h4>

                        <div
                            style="
                                display: flex;
                                flex-wrap: wrap;
                                gap: 8px;
                                margin-top: 15px;
                            "
                        >

                            @foreach ($workflowStages as $stageKey => $stageLabel)

                                @php

                                    $stageIndex =
                                        array_search(
                                            $stageKey,
                                            $workflowStageKeys,
                                            true
                                        );

                                    $isCurrent =
                                        $stageKey === $video->workflow_stage;

                                    $isCompleted =
                                        $currentStageIndex !== false &&
                                        $stageIndex < $currentStageIndex;

                                @endphp

                                <div
                                    style="
                                        padding: 8px 12px;
                                        border-radius: 4px;
                                        border: 1px solid #555;
                                        background:
                                            {{ $isCurrent
                                                ? '#444'
                                                : ($isCompleted
                                                    ? '#333'
                                                    : '#222') }};
                                        color:
                                            {{ $isCurrent
                                                ? '#fff'
                                                : ($isCompleted
                                                    ? '#aaa'
                                                    : '#666') }};
                                        font-size: 13px;
                                    "
                                >

                                    @if ($isCompleted)
                                        ✓
                                    @elseif ($isCurrent)
                                        ●
                                    @else
                                        ○
                                    @endif

                                    {{ $stageLabel }}

                                </div>

                            @endforeach

                        </div>

                        <div
                            style="
                                margin-top: 12px;
                                font-size: 13px;
                                color: #aaa;
                            "
                        >
                            現在：
                            <strong style="color: #fff;">
                                {{ $workflowStages[$video->workflow_stage] ?? $video->workflow_stage }}
                            </strong>
                        </div>

                    </div>

                    <hr>


                    {{-- 動画登録状態 --}}
                    <h4>
                        動画処理状況
                    </h4>

                    @if ($isVideoProcessing)

                        <div
                            class="alert alert-warning"
                            style="
                                margin-top: 15px;
                                margin-bottom: 15px;
                            "
                        >

                            <strong>
                                現在、動画を処理しています。
                            </strong>

                            <p style="margin: 8px 0 0 0;">
                                処理が完了するまで、
                                動画加工や出品者確認などの操作はできません。
                            </p>

                        </div>

                    @endif

                    {{-- 動画加工開始 --}}
                    @if (
                        !$isVideoProcessing &&
                        $video->ai_status === \App\Models\ProductVideoDraft::AI_APPROVED &&
                        $video->process_status === \App\Models\ProductVideoDraft::PROCESS_WAITING
                    )

                        <div
                            class="alert alert-info"
                            style="margin-top: 15px;"
                        >

                            <p style="margin-bottom: 15px;">
                                AI審査が承認されました。
                                動画加工方法を選択してください。
                            </p>


                            <form
                                method="POST"
                                action="{{ route(
                                    'seller.products.youtube-video.process',
                                    [
                                        'product' => $product->id,
                                        'video' => $video->id,
                                    ]
                                ) }}"
                                class="js-video-process-form"
                            >

                                @csrf


                            {{-- 加工モード --}}
                            <div class="form-group">
                                <label>動画加工モード</label>

                                <div>

                                    {{-- V1 --}}
                                    <label
                                        style="
                                            display: block;
                                            margin-bottom: 12px;
                                            cursor: pointer;
                                        "
                                    >
                                        <input
                                            type="radio"
                                            name="edit_mode"
                                            value="{{ \App\Models\ProductVideoDraft::EDIT_MODE_V1 }}"
                                            {{ old(
                                                'edit_mode',
                                                $video->edit_mode
                                            ) === \App\Models\ProductVideoDraft::EDIT_MODE_V1
                                                ? 'checked'
                                                : ''
                                            }}
                                        >

                                        <strong>V1</strong>

                                        <span style="margin-left: 8px;">
                                            ロゴ
                                        </span>
                                    </label>


                                    {{-- V2 --}}
                                    <label
                                        style="
                                            display: block;
                                            margin-bottom: 12px;
                                            cursor: pointer;
                                        "
                                    >
                                        <input
                                            type="radio"
                                            name="edit_mode"
                                            value="{{ \App\Models\ProductVideoDraft::EDIT_MODE_V2 }}"
                                            {{ old(
                                                'edit_mode',
                                                $video->edit_mode
                                            ) === \App\Models\ProductVideoDraft::EDIT_MODE_V2
                                                ? 'checked'
                                                : ''
                                            }}
                                        >

                                        <strong>V2</strong>

                                        <span style="margin-left: 8px;">
                                            ロゴ + BGM + エンディング
                                        </span>
                                    </label>

                                </div>
                            </div>


                            {{-- V2 BGM選択 --}}
                            <div
                                id="bgm-selection"
                                class="form-group"
                                style="
                                    margin-top: 20px;
                                    padding: 15px;
                                    border: 1px solid #444;
                                    border-radius: 6px;
                                "
                            >
                                <label
                                    style="
                                        display: block;
                                        margin-bottom: 12px;
                                        font-weight: bold;
                                    "
                                >
                                    BGMを選択
                                </label>

                                @if ($bgms->isEmpty())

                                    <p style="margin: 0;">
                                        現在、使用できるBGMがありません。
                                    </p>

                                @else

                                    @foreach ($bgms as $bgm)

                                        @if (isset($bgmUrls[$bgm->id]))

                                            <label
                                                style="
                                                    display: block;
                                                    margin-bottom: 12px;
                                                    cursor: pointer;
                                                "
                                            >

                                                <input
                                                    type="radio"
                                                    name="bgm_id"
                                                    value="{{ $bgm->id }}"
                                                    {{ (string) old(
                                                        'bgm_id',
                                                        $video->bgm_id
                                                    ) === (string) $bgm->id
                                                        ? 'checked'
                                                        : ''
                                                    }}
                                                >

                                                <span style="margin-left: 8px;">
                                                    {{ $bgm->name }}
                                                </span>

                                                @if (!empty($bgm->duration))
                                                    <span
                                                        style="
                                                            margin-left: 8px;
                                                            opacity: 0.7;
                                                        "
                                                    >
                                                        {{ number_format($bgm->duration, 1) }}秒
                                                    </span>
                                                @endif

                                                <audio
                                                    controls
                                                    preload="none"
                                                    style="
                                                        display: block;
                                                        width: 100%;
                                                        max-width: 500px;
                                                        margin-top: 8px;
                                                    "
                                                >
                                                    <source
                                                        src="{{ $bgmUrls[$bgm->id] }}"
                                                        type="audio/mpeg"
                                                    >

                                                    お使いのブラウザでは
                                                    音声を再生できません。
                                                </audio>

                                            </label>

                                        @endif

                                    @endforeach

                                @endif
                            </div>




                                {{-- 開始ボタン --}}
                                <button
                                    type="submit"
                                    class="btn btn-primary js-video-process-button"
                                >
                                    選択した方法で動画加工を開始
                                </button>

                            </form>

                        </div>

                    @endif

                    <table class="table table-bordered">

                        <tbody>

                            <tr>

                                <th style="width: 40%;">
                                    AI審査
                                </th>

                                <td>
                                    {{ $video->ai_status }}
                                </td>

                            </tr>


                            <tr>

                                <th>
                                    動画加工
                                </th>

                                <td>
                                    {{ $video->process_status }}
                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Preview
                                </th>

                                <td>
                                    {{ $video->preview_status }}
                                </td>

                            </tr>


                            <tr>

                                <th>
                                    出品者確認
                                </th>

                                <td>
                                    {{ $video->seller_review_status }}
                                </td>

                            </tr>


                            <tr>

                                <th>
                                    管理者審査
                                </th>

                                <td>
                                    {{ $video->review_status }}
                                </td>

                            </tr>


                            <tr>

                                <th>
                                    YouTube
                                </th>

                                <td>
                                    {{ $video->youtube_status }}
                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Workflow
                                </th>

                                <td>
                                    {{ $video->workflow_stage }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                    {{-- ==========================================
                     管理者審査NG
                    ========================================== --}}
                    @if (
                        $video->review_status
                            === \App\Models\ProductVideoDraft::REVIEW_REJECTED
                    )

                        <div
                            class="alert alert-danger"
                            style="margin-top:20px;"
                        >
                            <h4>
                                この動画は掲載不可となりました
                            </h4>

                            <p>
                                管理者審査の結果、
                                この動画は公開できません。
                            </p>

                            @if(!empty($video->review_reject_reason))
                                <p>
                                    <strong>理由：</strong>
                                    {{ $video->review_reject_reason }}
                                </p>
                            @endif

                            <hr>

                            <p>
                                修正後、新しい動画を登録してください。
                            </p>

                            <a
                                href="{{ route(
                                    'seller.products.youtube-video.create',
                                    ['product' => $product->id]
                                ) }}"
                                class="btn btn-primary"
                            >
                                新しい動画を登録
                            </a>
                        </div>

                    @endif

                    {{-- 出品者確認 --}}
                    @if (
                        !$isVideoProcessing &&
                        $video->preview_status === \App\Models\ProductVideoDraft::PREVIEW_COMPLETED &&
                        $video->seller_review_status === \App\Models\ProductVideoDraft::SELLER_REVIEW_PENDING
                    )

                        <div
                            class="alert alert-info"
                            style="margin-top: 15px;"
                        >

                            <p style="margin-bottom: 10px;">
                                Previewの確認が完了したら、
                                出品者確認を完了してください。
                            </p>

                            <form
                                method="POST"
                                action="{{ route(
                                    'seller.products.youtube-video.seller-review',
                                    [
                                        'product' => $product->id,
                                        'video' => $video->id,
                                    ]
                                ) }}"
                            >

                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    この動画を承認する
                                </button>

                            </form>

                        </div>

                    @endif


                    <hr>


                    {{-- 登録日時 --}}
                    <div class="form-group">

                        <label>
                            登録日時
                        </label>

                        <div
                            class="form-control"
                            style="
                                height: auto;
                                background: #f8f8f8;
                            "
                        >

                            {{ $video->created_at }}

                        </div>

                    </div>


                    {{-- 戻る --}}
                    <div
                        class="form-group"
                        style="margin-top: 20px;"
                    >

                        <a
                            href="{{ route(
                                'seller.product_drafts.edit',
                                ['draft' => $product->draft_id]
                            ) }}"
                            class="btn btn-default"
                        >
                            商品編集画面へ戻る
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form =
        document.querySelector('.js-video-process-form');

    if (!form) return;


    const button =
        form.querySelector('.js-video-process-button');


    const editModeRadios =
        form.querySelectorAll(
            'input[name="edit_mode"]'
        );


    const bgmSelection =
        document.getElementById(
            'bgm-selection'
        );


    const bgmRadios =
        form.querySelectorAll(
            'input[name="bgm_id"]'
        );


    /*
    |--------------------------------------------------------------------------
    | BGM表示切替
    |--------------------------------------------------------------------------
    */

    function updateBgmVisibility() {

        let selectedMode = null;

        editModeRadios.forEach(function (radio) {

            if (radio.checked) {
                selectedMode = radio.value;
            }

        });


        const isV2 =
            selectedMode ===
            '{{ \App\Models\ProductVideoDraft::EDIT_MODE_V2 }}';


        if (bgmSelection) {
            bgmSelection.style.display =
                isV2 ? 'block' : 'none';
        }


        /*
        |--------------------------------------------------------------------------
        | V1ではBGM選択を解除
        |--------------------------------------------------------------------------
        */

        if (!isV2) {

            bgmRadios.forEach(function (radio) {
                radio.checked = false;
            });

        }

    }


    editModeRadios.forEach(function (radio) {

        radio.addEventListener(
            'change',
            updateBgmVisibility
        );

    });


    updateBgmVisibility();


    /*
    |--------------------------------------------------------------------------
    | 二重送信防止
    |--------------------------------------------------------------------------
    */

    if (!button) return;


    form.addEventListener('submit', function () {

        if (form.dataset.submitted === '1') {
            return;
        }


        form.dataset.submitted = '1';


        button.disabled = true;


        button.innerText =
            '動画加工を開始しています…';

    });

});

</script>

@endsection