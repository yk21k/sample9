@extends('layouts.seller')

@section('content')

<style>

    .edit-page {

        max-width: 1400px;

        margin: 0 auto;

        padding-bottom: 120px;

    }


    .edit-mode-card {

        border: 2px solid #e5e5e5;

        border-radius: 8px;

        padding: 18px;

        margin-bottom: 12px;

        cursor: pointer;

        transition: 0.2s;

    }


    .edit-mode-card:hover {

        border-color: #337ab7;

        background: #f8fbff;

    }


    .edit-mode-card.active {

        border-color: #337ab7;

        background: #f5f9fd;

    }


    .edit-mode-radio {

        margin-right: 8px !important;

    }


    .edit-mode-title {

        font-size: 16px;

        font-weight: bold;

    }


    .edit-mode-description {

        margin-top: 6px;

        margin-left: 24px;

        color: #777;

        font-size: 13px;

    }


    .video-preview {

        width: 100%;

        max-height: 500px;

        background: #000;

        border-radius: 6px;

    }


    .setting-box {

        margin-top: 15px;

        padding: 15px;

        background: #f8f8f8;

        border-radius: 6px;

    }


    .feature-list {

        margin: 8px 0 0 24px;

        padding: 0;

        color: #777;

        font-size: 13px;

    }


    .workflow-bar {

        margin-bottom: 20px;

        padding: 15px;

        background: #f8f8f8;

        border-radius: 6px;

        text-align: center;

        font-size: 13px;

    }


    .workflow-current {

        font-weight: bold;

        color: #337ab7;

    }


    .review-panel {

        border: 2px solid #f0ad4e;

    }


    .review-panel.approved {

        border-color: #5cb85c;

    }


    .review-title {

        font-size: 16px;

        font-weight: bold;

    }


    .review-message {

        margin-bottom: 15px;

        line-height: 1.7;

    }

</style>


<div class="container-fluid edit-page">


{{-- ========================================================= --}}
{{-- Header --}}
{{-- ========================================================= --}}

<div class="row">

    <div class="col-md-8">

        <h2>
            動画加工・承認
        </h2>

        <p class="text-muted">

            {{ $video->title }}

        </p>

    </div>


    <div class="col-md-4 text-right">

        <a
            href="{{ route(
                'seller.shop-videos.show',
                $video
            ) }}"
            class="btn btn-default"
        >

            詳細へ戻る

        </a>

    </div>

</div>


<br>



{{-- ========================================================= --}}
{{-- Workflow --}}
{{-- ========================================================= --}}

<div class="workflow-bar">

    Upload

    <span class="text-muted">→</span>

    AI審査

    <span class="text-muted">→</span>

    <span class="workflow-current">
        動画加工
    </span>

    <span class="text-muted">→</span>

    出品者確認

    <span class="text-muted">→</span>

    管理者審査

    <span class="text-muted">→</span>

    SNS

    <span class="text-muted">→</span>

    Shop公開

</div>



<div class="row">


{{-- ===================================================== --}}
{{-- 左側：動画 --}}
{{-- ===================================================== --}}

<div class="col-md-7">


    <div class="panel panel-bordered">

        <div class="panel-heading">

            <strong>
                加工後動画
            </strong>

        </div>


        <div class="panel-body">

            @if($video->processed_movie)

                <video
                    controls
                    playsinline
                    class="video-preview"
                >
                    <source
                        src="{{ Storage::disk('s3')->temporaryUrl(
                            $video->processed_movie,
                            now()->addMinutes(30)
                        ) }}"
                        type="{{ $video->mime_type ?? 'video/mp4' }}"
                    >
                </video>

            @else

                <div class="alert alert-info">

                    まだ加工後動画はありません。

                    <br>

                    右側から編集設定を選択して
                    「動画を加工する」を実行してください。

                </div>

            @endif

        </div>

    </div>



    {{-- ================================================= --}}
    {{-- 動画情報 --}}
    {{-- ================================================= --}}

    <div class="panel panel-bordered">

        <div class="panel-heading">

            <strong>
                動画情報
            </strong>

        </div>


        <div class="panel-body">

            <table class="table table-condensed">

                <tr>

                    <th style="width:35%;">
                        タイトル
                    </th>

                    <td>
                        {{ $video->title }}
                    </td>

                </tr>


                <tr>

                    <th>
                        再生時間
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
                        動画加工
                    </th>

                    <td>

                        @if(
                            $video->process_status
                            === \App\Models\ShopVideoDraft::PROCESS_COMPLETED
                        )

                            <span class="label label-success">
                                完成
                            </span>

                        @elseif(
                            $video->process_status
                            === \App\Models\ShopVideoDraft::PROCESS_RUNNING
                        )

                            <span class="label label-warning">
                                加工中
                            </span>

                        @elseif(
                            $video->process_status
                            === \App\Models\ShopVideoDraft::PROCESS_FAILED
                        )

                            <span class="label label-danger">
                                エラー
                            </span>

                        @else

                            <span class="label label-default">
                                未加工
                            </span>

                        @endif

                    </td>

                </tr>


                <tr>

                    <th>
                        編集モード
                    </th>

                    <td>

                        {{ strtoupper(
                            $video->edit_mode
                            ?? 'pending'
                        ) }}

                    </td>

                </tr>


                <tr>

                    <th>
                        出品者確認
                    </th>

                    <td>

                        @if(
                            $video->seller_review_status
                            === \App\Models\ShopVideoDraft::SELLER_REVIEW_APPROVED
                        )

                            <span class="label label-success">
                                承認済み
                            </span>

                        @elseif(
                            $video->seller_review_status
                            === \App\Models\ShopVideoDraft::SELLER_REVIEW_PENDING
                        )

                            <span class="label label-warning">
                                確認待ち
                            </span>

                        @else

                            <span class="label label-default">
                                未確認
                            </span>

                        @endif

                    </td>

                </tr>

            </table>

        </div>

    </div>


</div>



{{-- ===================================================== --}}
{{-- 右側 --}}
{{-- ===================================================== --}}

<div class="col-md-5">


{{-- ===================================================== --}}
{{-- 編集設定 --}}
{{-- ===================================================== --}}

<form
    method="POST"
    action="{{ route(
        'seller.shop-videos.update',
        $video
    ) }}"
    id="video-edit-form"

    data-saved-edit-mode="{{ $video->edit_mode }}"
    data-saved-bgm-id="{{ $video->bgm_id ?? '' }}"
>

    @csrf

    @method('PUT')



    <div class="panel panel-bordered">

        <div class="panel-heading">

            <strong>
                編集モード
            </strong>

        </div>


        <div class="panel-body">


            {{-- V1 --}}

            <label
                class="edit-mode-card
                {{ old(
                    'edit_mode',
                    $video->edit_mode
                ) === 'v1'
                    ? 'active'
                    : '' }}"
            >

                <input
                    type="radio"
                    name="edit_mode"
                    value="v1"
                    class="edit-mode-radio"

                    {{ old(
                        'edit_mode',
                        $video->edit_mode
                    ) === 'v1'
                        ? 'checked'
                        : '' }}
                >

                <span class="edit-mode-title">

                    V1：基本編集

                </span>


                <div class="edit-mode-description">

                    ロゴを追加して動画を加工します。

                </div>


                <ul class="feature-list">

                    <li>
                        店舗ロゴ
                    </li>

                </ul>

            </label>



            {{-- V2 --}}

            <label
                class="edit-mode-card
                {{ old(
                    'edit_mode',
                    $video->edit_mode
                ) === 'v2'
                    ? 'active'
                    : '' }}"
            >

                <input
                    type="radio"
                    name="edit_mode"
                    value="v2"
                    class="edit-mode-radio"

                    {{ old(
                        'edit_mode',
                        $video->edit_mode
                    ) === 'v2'
                        ? 'checked'
                        : '' }}
                >

                <span class="edit-mode-title">

                    V2：BGM付き

                </span>


                <div class="edit-mode-description">

                    ロゴ・BGM・エンディングを追加します。

                </div>


                <ul class="feature-list">

                    <li>
                        店舗ロゴ
                    </li>

                    <li>
                        BGM
                    </li>

                    <li>
                        エンディング
                    </li>

                </ul>

            </label>



            {{-- V3 --}}

            <label
                class="edit-mode-card
                {{ old(
                    'edit_mode',
                    $video->edit_mode
                ) === 'v3'
                    ? 'active'
                    : '' }}"
            >

                <input
                    type="radio"
                    name="edit_mode"
                    value="v3"
                    class="edit-mode-radio"

                    {{ old(
                        'edit_mode',
                        $video->edit_mode
                    ) === 'v3'
                        ? 'checked'
                        : '' }}
                >

                <span class="edit-mode-title">

                    V3：AI動画

                </span>


                <div class="edit-mode-description">

                    より高度な動画加工を行います。

                </div>


                <ul class="feature-list">

                    <li>
                        店舗ロゴ
                    </li>

                    <li>
                        BGM
                    </li>

                    <li>
                        字幕
                    </li>

                    <li>
                        AIナレーション
                    </li>

                </ul>

            </label>

        </div>

    </div>



{{-- ===================================================== --}}
{{-- BGM --}}
{{-- ===================================================== --}}

<div
    class="panel panel-bordered"
    id="bgm-panel"
>

    <div class="panel-heading">

        <strong>
            BGM
        </strong>

    </div>


    <div class="panel-body">

        <div class="form-group">

            <label>
                使用するBGM
            </label>


            <select
                name="bgm_id"
                id="bgm_id"
                class="form-control"
            >

                <option value="">
                    BGMを選択してください
                </option>

                @foreach($bgms as $bgm)

                    <option
                        value="{{ $bgm->id }}"
                        data-audio-url="{{ $bgmUrls[$bgm->id] ?? '' }}"

                        {{ old(
                            'bgm_id',
                            $video->bgm_id
                        ) == $bgm->id
                            ? 'selected'
                            : '' }}
                    >

                        {{ $bgm->name }}

                        @if($bgm->duration)

                            （{{ gmdate(
                                'i:s',
                                (int) $bgm->duration
                            ) }}）

                        @endif

                    </option>

                @endforeach

            </select>


            <div
                id="bgm-preview-area"
                style="margin-top:15px;"
            >

                <button
                    type="button"
                    class="btn btn-info"
                    id="bgm-preview-button"
                    disabled
                >

                    <i class="fas fa-play"></i>

                    BGMを試聴

                </button>


                <button
                    type="button"
                    class="btn btn-default"
                    id="bgm-stop-button"
                    disabled
                >

                    <i class="fas fa-stop"></i>

                    停止

                </button>

            </div>


            <audio
                id="bgm-audio"
                preload="none"
            ></audio>

        </div>


        <div
            class="alert alert-info"
            style="margin-bottom:0;"
        >

            V2・V3ではBGMを使用できます。

        </div>

    </div>

</div>



{{-- ===================================================== --}}
{{-- V3 --}}
{{-- ===================================================== --}}

<div
    class="panel panel-bordered"
    id="v3-panel"
>

    <div class="panel-heading">

        <strong>
            V3機能
        </strong>

    </div>


    <div class="panel-body">

        <div class="alert alert-info">

            V3の字幕・AIナレーション設定は
            今後追加します。

        </div>

    </div>

</div>



{{-- ===================================================== --}}
{{-- 保存 --}}
{{-- ===================================================== --}}

<div class="panel panel-bordered">

    <div class="panel-body">

        <button
            type="submit"
            class="btn btn-primary btn-block"
            id="save-button"
        >

            編集設定を保存

        </button>

    </div>

</div>

</form>



{{-- ===================================================== --}}
{{-- 動画加工 --}}
{{-- ===================================================== --}}

<form
    method="POST"
    action="{{ route(
        'seller.shop-videos.process',
        $video
    ) }}"
    id="video-process-form"
>

    @csrf


    <button
        type="submit"
        class="btn btn-success btn-block"
        id="process-button"
        disabled
    >

        動画を加工する

    </button>

</form>



<br>



{{-- ===================================================== --}}
{{-- 出品者確認・承認 --}}
{{-- ===================================================== --}}

@if(
    $video->process_status
    === \App\Models\ShopVideoDraft::PROCESS_COMPLETED
)

    <div
        class="panel panel-bordered review-panel
        {{ $video->seller_review_status
            === \App\Models\ShopVideoDraft::SELLER_REVIEW_APPROVED
            ? 'approved'
            : '' }}"
    >

        <div class="panel-heading">

            <strong class="review-title">

                出品者確認

            </strong>

        </div>


        <div class="panel-body">


            @if(
                $video->seller_review_status
                === \App\Models\ShopVideoDraft::SELLER_REVIEW_PENDING
            )

                <div class="review-message">

                    <strong>
                        加工後の動画を確認してください。
                    </strong>

                    <br><br>

                    左側の動画を最後まで確認し、
                    問題がなければ承認してください。

                </div>


                <form
                    method="POST"
                    action="{{ route(
                        'seller.shop-videos.seller-review.approve',
                        ['shopVideo' => $video->id]
                    ) }}"
                    id="seller-review-form"
                >

                    @csrf


                    <button
                        type="submit"
                        class="btn btn-success btn-block"
                        id="seller-approve-button"
                    >

                        この動画を承認する

                    </button>

                </form>


            @elseif(
                $video->seller_review_status
                === \App\Models\ShopVideoDraft::SELLER_REVIEW_APPROVED
            )

                <div class="alert alert-success mb-0">

                    <strong>
                        この動画は承認済みです。
                    </strong>

                    <br>

                    次は管理者審査へ進みます。

                </div>


            @else

                <div class="alert alert-secondary mb-0">

                    出品者確認の準備中です。

                </div>

            @endif


        </div>

    </div>

@endif


</div>

</div>

</div>



<script>

(function () {


    /*
    |--------------------------------------------------------------------------
    | 編集モード
    |--------------------------------------------------------------------------
    */

    const radios =
        document.querySelectorAll(
            'input[name="edit_mode"]'
        );


    const bgmPanel =
        document.getElementById(
            'bgm-panel'
        );


    const v3Panel =
        document.getElementById(
            'v3-panel'
        );



    /*
    |--------------------------------------------------------------------------
    | BGM
    |--------------------------------------------------------------------------
    */

    const bgmSelect =
        document.getElementById(
            'bgm_id'
        );


    const bgmPreviewButton =
        document.getElementById(
            'bgm-preview-button'
        );


    const bgmStopButton =
        document.getElementById(
            'bgm-stop-button'
        );


    const bgmAudio =
        document.getElementById(
            'bgm-audio'
        );



    /*
    |--------------------------------------------------------------------------
    | 編集フォーム
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById(
            'video-edit-form'
        );


    const saveButton =
        document.getElementById(
            'save-button'
        );



    /*
    |--------------------------------------------------------------------------
    | 加工フォーム
    |--------------------------------------------------------------------------
    */

    const processForm =
        document.getElementById(
            'video-process-form'
        );


    const processButton =
        document.getElementById(
            'process-button'
        );



    /*
    |--------------------------------------------------------------------------
    | 承認フォーム
    |--------------------------------------------------------------------------
    */

    const reviewForm =
        document.getElementById(
            'seller-review-form'
        );


    const approveButton =
        document.getElementById(
            'seller-approve-button'
        );



    /*
    |--------------------------------------------------------------------------
    | 保存済み設定
    |--------------------------------------------------------------------------
    */

    const savedEditMode =
        form
            ? form.dataset.savedEditMode
            : null;


    const savedBgmId =
        form
            ? form.dataset.savedBgmId
            : '';



    /*
    |--------------------------------------------------------------------------
    | 現在の編集モード
    |--------------------------------------------------------------------------
    */

    function getSelectedEditMode() {

        let mode = null;


        radios.forEach(
            function (radio) {

                if (radio.checked) {

                    mode = radio.value;

                }

            }
        );


        return mode;

    }



    /*
    |--------------------------------------------------------------------------
    | BGM
    |--------------------------------------------------------------------------
    */

    function getSelectedBgmId() {

        if (!bgmSelect) {

            return '';

        }


        return bgmSelect.value || '';

    }



    /*
    |--------------------------------------------------------------------------
    | 設定有効性
    |--------------------------------------------------------------------------
    */

    function isCurrentSettingValid() {

        const mode =
            getSelectedEditMode();


        const bgmId =
            getSelectedBgmId();


        if (!mode) {

            return false;

        }


        if (
            mode === 'v2'
            ||
            mode === 'v3'
        ) {

            if (!bgmId) {

                return false;

            }

        }


        return true;

    }



    /*
    |--------------------------------------------------------------------------
    | 保存済みか
    |--------------------------------------------------------------------------
    */

    function isSettingSaved() {

        const currentEditMode =
            getSelectedEditMode();


        const currentBgmId =
            getSelectedBgmId();


        if (
            currentEditMode
            !== savedEditMode
        ) {

            return false;

        }


        if (
            currentEditMode === 'v1'
        ) {

            return true;

        }


        return (
            currentBgmId
            === savedBgmId
        );

    }



    /*
    |--------------------------------------------------------------------------
    | ボタン状態
    |--------------------------------------------------------------------------
    */

    function updateButtons() {

        const valid =
            isCurrentSettingValid();


        const saved =
            isSettingSaved();


        if (saveButton) {

            saveButton.disabled =
                !valid || saved;

        }


        if (processButton) {

            processButton.disabled =
                !valid || !saved;

        }

    }



    /*
    |--------------------------------------------------------------------------
    | BGM停止
    |--------------------------------------------------------------------------
    */

    function stopBgm() {

        if (!bgmAudio) {

            return;

        }


        bgmAudio.pause();

        bgmAudio.currentTime = 0;


        if (bgmPreviewButton) {

            bgmPreviewButton.disabled =
                true;

            bgmPreviewButton.innerHTML =
                '<i class="fas fa-play"></i> BGMを試聴';

        }


        if (bgmStopButton) {

            bgmStopButton.disabled =
                true;

        }

    }



    /*
    |--------------------------------------------------------------------------
    | BGMプレビュー
    |--------------------------------------------------------------------------
    */

    function updateBgmPreview() {

        if (
            !bgmSelect ||
            !bgmPreviewButton ||
            !bgmStopButton ||
            !bgmAudio
        ) {

            return;

        }


        stopBgm();


        const option =
            bgmSelect.options[
                bgmSelect.selectedIndex
            ];


        if (
            !option ||
            !option.value
        ) {

            bgmAudio.removeAttribute(
                'src'
            );

            bgmAudio.load();

            return;

        }


        const url =
            option.dataset.audioUrl;


        if (!url) {

            return;

        }


        bgmPreviewButton.disabled =
            false;


        bgmStopButton.disabled =
            true;


        bgmAudio.src =
            url;

        bgmAudio.load();


        bgmPreviewButton.innerHTML =
            '<i class="fas fa-play"></i> BGMを試聴';

    }



    /*
    |--------------------------------------------------------------------------
    | 編集モード更新
    |--------------------------------------------------------------------------
    */

    function updateEditMode() {

        let mode = null;


        radios.forEach(
            function (radio) {

                if (radio.checked) {

                    mode =
                        radio.value;

                }


                const card =
                    radio.closest(
                        '.edit-mode-card'
                    );


                if (!card) {

                    return;

                }


                if (radio.checked) {

                    card.classList.add(
                        'active'
                    );

                } else {

                    card.classList.remove(
                        'active'
                    );

                }

            }
        );


        if (
            mode === 'v2'
            ||
            mode === 'v3'
        ) {

            if (bgmPanel) {

                bgmPanel.style.display =
                    'block';

            }

        } else {

            if (bgmPanel) {

                bgmPanel.style.display =
                    'none';

            }


            stopBgm();

        }


        if (mode === 'v3') {

            if (v3Panel) {

                v3Panel.style.display =
                    'block';

            }

        } else {

            if (v3Panel) {

                v3Panel.style.display =
                    'none';

            }

        }


        updateButtons();

    }



    /*
    |--------------------------------------------------------------------------
    | 編集モード変更
    |--------------------------------------------------------------------------
    */

    radios.forEach(
        function (radio) {

            radio.addEventListener(
                'change',
                function () {

                    updateEditMode();

                }
            );

        }
    );



    /*
    |--------------------------------------------------------------------------
    | BGM変更
    |--------------------------------------------------------------------------
    */

    if (bgmSelect) {

        bgmSelect.addEventListener(
            'change',
            function () {

                updateBgmPreview();

                updateButtons();

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | BGM再生
    |--------------------------------------------------------------------------
    */

    if (
        bgmPreviewButton &&
        bgmAudio
    ) {

        bgmPreviewButton.addEventListener(
            'click',
            function () {

                if (!bgmAudio.src) {

                    return;

                }


                if (!bgmAudio.paused) {

                    bgmAudio.pause();

                    bgmPreviewButton.innerHTML =
                        '<i class="fas fa-play"></i> BGMを試聴';

                    return;

                }


                bgmAudio.play()
                    .then(
                        function () {

                            bgmPreviewButton.innerHTML =
                                '<i class="fas fa-pause"></i> 試聴を停止';


                            if (bgmStopButton) {

                                bgmStopButton.disabled =
                                    false;

                            }

                        }
                    )
                    .catch(
                        function (error) {

                            console.error(
                                'BGM playback error:',
                                error
                            );

                        }
                    );

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | BGM停止
    |--------------------------------------------------------------------------
    */

    if (
        bgmStopButton &&
        bgmAudio
    ) {

        bgmStopButton.addEventListener(
            'click',
            function () {

                stopBgm();


                if (
                    bgmSelect &&
                    bgmSelect.value
                ) {

                    const option =
                        bgmSelect.options[
                            bgmSelect.selectedIndex
                        ];


                    const url =
                        option?.dataset.audioUrl;


                    if (url) {

                        bgmPreviewButton.disabled =
                            false;

                    }

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | BGM終了
    |--------------------------------------------------------------------------
    */

    if (bgmAudio) {

        bgmAudio.addEventListener(
            'ended',
            function () {

                bgmAudio.currentTime =
                    0;


                if (bgmPreviewButton) {

                    bgmPreviewButton.disabled =
                        false;

                    bgmPreviewButton.innerHTML =
                        '<i class="fas fa-play"></i> BGMを試聴';

                }


                if (bgmStopButton) {

                    bgmStopButton.disabled =
                        true;

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | 初期化
    |--------------------------------------------------------------------------
    */

    updateEditMode();

    updateBgmPreview();

    updateButtons();



    /*
    |--------------------------------------------------------------------------
    | 編集設定保存
    |--------------------------------------------------------------------------
    */

    if (form) {

        form.addEventListener(
            'submit',
            function () {

                if (saveButton) {

                    saveButton.disabled =
                        true;

                    saveButton.innerText =
                        '保存しています...';

                }


                if (processButton) {

                    processButton.disabled =
                        true;

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | 動画加工
    |--------------------------------------------------------------------------
    */

    if (processForm) {

        processForm.addEventListener(
            'submit',
            function () {

                if (processButton) {

                    processButton.disabled =
                        true;

                    processButton.innerText =
                        '加工を開始しています...';

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | 出品者承認
    |--------------------------------------------------------------------------
    */

    if (reviewForm) {

        reviewForm.addEventListener(
            'submit',
            function (event) {

                const confirmed =
                    window.confirm(
                        '加工後の動画を承認しますか？\n\n' +
                        '承認すると管理者審査へ進みます。'
                    );


                if (!confirmed) {

                    event.preventDefault();

                    return;

                }


                if (approveButton) {

                    approveButton.disabled =
                        true;

                    approveButton.innerText =
                        '承認しています...';

                }

            }
        );

    }


})();

</script>


@endsection