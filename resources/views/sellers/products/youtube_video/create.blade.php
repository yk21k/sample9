@extends('layouts.seller')

@section('content')

<div class="container">

    <div class="row">

        <div class="col-md-8 col-md-offset-2">

            <div class="panel panel-default">

                <div class="panel-heading">
                    商品SNS用動画登録
                </div>

                <div class="panel-body">

                    {{-- エラーメッセージ --}}
                    @if ($errors->any())

                        <div class="alert alert-danger">

                            <ul style="margin-bottom: 0;">

                                @foreach ($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- 成功・エラーメッセージ --}}
                    @if (session('success'))

                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>

                    @endif


                    @if (session('error'))

                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>

                    @endif


                    {{-- 商品情報 --}}
                    <div class="form-group">

                        <label>
                            商品
                        </label>

                        <div class="form-control"
                             style="height: auto; background: #f8f8f8;">

                            {{ $product->name }}

                        </div>

                    </div>


                    <hr>


                    {{-- 動画登録 --}}
                    <form
                        method="POST"
                        action="{{ route(
                            'seller.products.youtube-video.store',
                            ['product' => $product->id]
                        ) }}"
                        enctype="multipart/form-data"
                    >

                        @csrf


                        {{-- タイトル --}}
                        <div class="form-group">

                            <label for="title">
                                動画タイトル
                            </label>

                            <input
                                type="text"
                                id="title"
                                name="title"
                                class="form-control"
                                value="{{ old('title', $product->name) }}"
                                maxlength="255"
                            >

                            <p class="help-block">
                                未入力の場合は商品名が使用されます。
                            </p>

                        </div>


                        {{-- 説明 --}}
                        <div class="form-group">

                            <label for="description">
                                動画説明
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="5"
                            >{{ old('description', $product->description) }}</textarea>

                        </div>


                        {{-- 動画 --}}
                        <div class="form-group">

                            <label for="video">
                                SNS用動画
                                <span style="color: red;">
                                    *
                                </span>
                            </label>

                            <input
                                type="file"
                                id="video"
                                name="video"
                                class="form-control"
                                accept="video/mp4,video/quicktime,video/webm"
                                required
                            >

                            <p class="help-block">
                                MP4 / MOV / WebM
                                <br>
                                最大 1GB
                                <br>
                                ※登録後、SNS・YouTube向けに動画を加工します。
                            </p>

                        </div>


                        <hr>


                        {{-- 注意事項 --}}
                        <div class="alert alert-info">

                            <strong>
                                動画登録後の流れ
                            </strong>

                            <ol style="margin-top: 10px; margin-bottom: 0;">

                                <li>AI審査</li>

                                <li>動画加工</li>

                                <li>Preview生成</li>

                                <li>出品者確認</li>

                                <li>管理者審査</li>

                                <li>YouTubeアップロード</li>

                            </ol>

                        </div>


                        {{-- ボタン --}}
                        <div class="form-group"
                             style="margin-top: 20px;">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                商品動画を登録する
                            </button>

                            <a
                                href="{{ url()->previous() }}"
                                class="btn btn-default"
                                style="margin-left: 10px;"
                            >
                                戻る
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection