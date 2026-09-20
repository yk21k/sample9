@extends('voyager::master')

@section('content')

<div class="container-fluid">

    <h1 class="page-title">
        <i class="voyager-video-camera"></i>
        ショップ動画審査
    </h1>


    <div class="panel panel-bordered">

        <div class="panel-heading">
            <h3 class="panel-title">
                動画情報
            </h3>
        </div>


        <div class="panel-body">

            <div class="row">

                <div class="col-md-8">

                    <h4>Preview</h4>

                    @if($video->preview_url)

                        <hr>

                        <h4>加工済み動画</h4>

                        <video
                            controls
                            preload="metadata"
                            style="width:100%; max-height:500px;"
                        >
                            <source
                                src="{{ $video->processed_url }}"
                                type="video/mp4"
                            >
                        </video>

                    @else

                        <div class="alert alert-warning">
                            Preview動画がありません。
                        </div>

                    @endif

                </div>


                <div class="col-md-4">

                    @if($video->thumbnail_url)

                        <h4>サムネイル</h4>

                        <img
                            src="{{ $video->thumbnail_url }}"
                            class="img-responsive"
                            style="max-width:100%;"
                            alt="{{ $video->title }}"
                        >

                    @endif

                </div>

            </div>


            <hr>


            <h4>基本情報</h4>

            <table class="table table-bordered">

                <tr>
                    <th width="200">ID</th>
                    <td>{{ $video->id }}</td>
                </tr>

                <tr>
                    <th>店舗</th>
                    <td>{{ $video->shop->name ?? '-' }}</td>
                </tr>

                <tr>
                    <th>タイトル</th>
                    <td>{{ $video->title }}</td>
                </tr>

                <tr>
                    <th>説明</th>
                    <td>
                        {!! nl2br(e($video->description)) !!}
                    </td>
                </tr>

                <tr>
                    <th>AI判定</th>
                    <td>
                        {{ $video->ai_status }}
                    </td>
                </tr>

                <tr>
                    <th>AIリスクスコア</th>
                    <td>
                        {{ $video->risk_score }}
                    </td>
                </tr>

                <tr>
                    <th>AI Provider</th>
                    <td>
                        {{ $video->ai_provider }}
                    </td>
                </tr>

                <tr>
                    <th>加工状態</th>
                    <td>
                        {{ $video->process_status }}
                    </td>
                </tr>

                <tr>
                    <th>加工動画</th>
                    <td>
                        {{ $video->processed_movie ?? '-' }}
                    </td>
                </tr>

            </table>


            <hr>


            <h4>AI検出ラベル</h4>

            @if(!empty($video->moderation_labels))

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>ラベル</th>
                            <th>Confidence</th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach($video->moderation_labels as $label)

                        <tr>

                            <td>
                                {{ $label['name'] ?? '-' }}
                            </td>

                            <td>
                                {{ $label['confidence'] ?? 0 }}
                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            @else

                <div class="alert alert-success">
                    AIによる検出ラベルはありません。
                </div>

            @endif


            <hr>


            <div class="text-center">

                <form
                    method="POST"
                    action="{{ route(
                        'admin.shop-video-reviews.approve',
                        ['video' => $video->id]
                    ) }}"
                    style="display:inline-block;"
                    onsubmit="return confirm('この動画を承認してYouTubeへのアップロードを開始します。よろしいですか？');"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        <i class="voyager-check"></i>
                        承認
                    </button>
                </form>


                <form
                    method="POST"
                    action="{{ route(
                        'admin.shop-video-reviews.reject',
                        ['video' => $video->id]
                    ) }}"
                    style="display:inline-block; margin-left:10px;"
                    onsubmit="return confirm('この動画を却下します。よろしいですか？');"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        <i class="voyager-x"></i>
                        却下
                    </button>
                </form>

            </div>

        </div>

    </div>

</div>

@endsection