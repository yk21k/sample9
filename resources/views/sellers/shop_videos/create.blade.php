@extends('layouts.seller')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-md-12">

            <h2>
                Shop動画登録
            </h2>

        </div>

    </div>


    @if ($errors->any())

        <div class="alert alert-danger">

            <strong>
                入力内容を確認してください。
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <div class="panel panel-bordered">

        <div class="panel-heading">

            <strong>
                新しい動画を登録
            </strong>

        </div>


        <div class="panel-body">

            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('seller.shop-videos.store') }}"
                id="shop-video-form"
            >

                @csrf


                {{-- タイトル --}}

                <div class="form-group">

                    <label for="title">

                        タイトル

                        <span class="text-danger">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}"
                        maxlength="255"
                        required
                    >

                    @error('title')

                        <span class="help-block text-danger">

                            {{ $message }}

                        </span>

                    @enderror

                </div>


                {{-- 説明 --}}

                <div class="form-group">

                    <label for="description">

                        説明

                    </label>

                    <textarea
                        name="description"
                        id="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="5"
                    >{{ old('description') }}</textarea>

                    @error('description')

                        <span class="help-block text-danger">

                            {{ $message }}

                        </span>

                    @enderror

                </div>


                {{-- 動画 --}}

                <div class="form-group">

                    <label for="movie">

                        動画

                        <span class="text-danger">
                            *
                        </span>

                    </label>

                    <input
                        type="file"
                        name="movie"
                        id="movie"
                        class="form-control @error('movie') is-invalid @enderror"
                        accept="video/mp4,video/quicktime,video/webm"
                        required
                    >

                    <p class="help-block">

                        対応形式：MP4 / MOV / WebM

                        <br>

                        最大サイズ：200MB

                    </p>

                    @error('movie')

                        <span class="help-block text-danger">

                            {{ $message }}

                        </span>

                    @enderror

                </div>


                {{-- ファイル情報 --}}

                <div
                    id="movie-info"
                    class="alert alert-info"
                    style="display:none;"
                >

                    <strong>
                        選択されたファイル
                    </strong>

                    <div id="movie-name"></div>

                    <div id="movie-size"></div>

                </div>


                {{-- Buttons --}}

                <div class="form-group">

                    <a
                        href="{{ route('seller.shop-videos.index') }}"
                        class="btn btn-default"
                    >

                        戻る

                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="submit-button"
                    >

                        <i class="voyager-upload"></i>

                        保存

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<script>

document
    .getElementById('movie')
    .addEventListener(
        'change',
        function () {

            const file =
                this.files[0];

            const info =
                document.getElementById(
                    'movie-info'
                );

            const name =
                document.getElementById(
                    'movie-name'
                );

            const size =
                document.getElementById(
                    'movie-size'
                );

            if (!file) {

                info.style.display =
                    'none';

                return;

            }

            const sizeMB =
                (
                    file.size
                    / 1024
                    / 1024
                ).toFixed(1);

            name.innerText =
                'ファイル名：'
                + file.name;

            size.innerText =
                'サイズ：'
                + sizeMB
                + ' MB';

            info.style.display =
                'block';

        }
    );


document
    .getElementById('shop-video-form')
    .addEventListener(
        'submit',
        function () {

            const button =
                document.getElementById(
                    'submit-button'
                );

            button.disabled =
                true;

            button.innerText =
                'アップロード中...';

        }
    );

</script>

@endsection