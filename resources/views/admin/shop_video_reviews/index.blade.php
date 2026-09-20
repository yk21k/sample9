@extends('voyager::master')

@section('content')

<div class="container-fluid">

<h1 class="page-title">
    <i class="voyager-video-camera"></i>
    ショップ動画審査
</h1>

<div class="panel panel-bordered">

    <div class="panel-body">

        <table class="table table-striped table-hover">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>サムネイル</th>
                    <th>店舗</th>
                    <th>タイトル</th>
                    <th>AI状態</th>
                    <th>加工状態</th>
                    <th>操作</th>
                </tr>
            </thead>

            <tbody>

            @forelse($videos as $video)

                <tr>

                    <td>
                        {{ $video->id }}
                    </td>

                    <td>
                        @if($video->thumbnail)

                            <img
                                src="{{ Storage::disk('s3')->temporaryUrl(
                                    $video->thumbnail,
                                    now()->addMinutes(10)
                                ) }}"
                                alt="{{ $video->title }}"
                                style="
                                    width: 120px;
                                    height: 68px;
                                    object-fit: cover;
                                    border-radius: 4px;
                                "
                            >

                        @else

                            <span class="text-muted">
                                サムネイルなし
                            </span>

                        @endif
                    </td>

                    <td>
                        {{ $video->shop->name ?? '-' }}
                    </td>

                    <td>
                        {{ $video->title }}
                    </td>

                    <td>

                        @if($video->ai_status === 'approved')

                            <span class="label label-success">
                                OK
                            </span>

                        @elseif($video->ai_status === 'warning')

                            <span class="label label-warning">
                                WARNING
                            </span>

                        @elseif($video->ai_status === 'processing')

                            <span class="label label-info">
                                AI解析中
                            </span>

                        @elseif($video->ai_status === 'pending')

                            <span class="label label-default">
                                待機中
                            </span>

                        @else

                            <span class="label label-danger">
                                NG
                            </span>

                        @endif

                    </td>

                    <td>

                        @if($video->process_status === 'completed')

                            <span class="label label-success">
                                完了
                            </span>

                        @elseif($video->process_status === 'processing')

                            <span class="label label-info">
                                加工中
                            </span>

                        @elseif($video->process_status === 'waiting')

                            <span class="label label-default">
                                待機中
                            </span>

                        @else

                            <span class="label label-danger">
                                {{ $video->process_status }}

                            </span>

                        @endif

                    </td>

                    <td>

                        <a
                            href="{{ route(
                                'admin.shop-video-reviews.show',
                                ['video' => $video->id]
                            ) }}"
                            class="btn btn-primary btn-sm"
                        >
                            <i class="voyager-eye"></i>
                            確認
                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted"
                    >
                        現在、審査待ちの動画はありません。
                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

        {{ $videos->links() }}

    </div>

</div>


</div>

@endsection
