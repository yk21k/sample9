@extends('layouts.public')

@section('content')
<div class="container py-5">

    <div class="mb-4">
        <a href="{{ route('seller.qanda.index') }}" class="text-muted">
            ← よくある質問一覧へ戻る
        </a>
    </div>

    <article class="card shadow-sm">
        <div class="card-body p-4">

            <h1 class="fw-bold mb-4">
                Q. {{ $qa->question }}
            </h1>

            <div class="qa-answer">
                {!! $qa->answer !!}
            </div>

        </div>
    </article>

</div>
@endsection
