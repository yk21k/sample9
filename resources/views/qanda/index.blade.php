@extends('layouts.public')

@section('content')
<div class="container py-5">

    <div class="mb-5 text-center">
        <h1 class="fw-bold">よくある質問（Q&A）</h1>
        <p class="text-muted mt-3">
            出店前・出店後によくあるご質問をまとめています。<br>
            チャットが使えない場合も、こちらからご確認いただけます。
        </p>
    </div>

    @foreach($qandas as $category => $items)
        <div class="mb-5">
            <h3 class="fw-bold mb-3">
                {{ $category }} に関する質問
            </h3>

            <div class="list-group">
                @foreach($items as $qa)
                    <a
                        href="{{ route('seller.qanda.show', $qa->slug) }}"
                        class="list-group-item list-group-item-action"
                    >
                        <div class="fw-semibold">
                            Q. {{ $qa->question }}
                        </div>
                        <small class="text-muted">
                            詳細を見る →
                        </small>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- BotMan 誘導 --}}
    <div class="mt-5 p-4 border rounded text-center">
        <h5 class="fw-bold">チャットで質問したい方へ</h5>
        <p class="text-muted mb-2">
            ログイン後は、FAQチャット（BotMan）から<br>
            キーワードで質問することもできます。
        </p>
        <small class="text-muted">
            ※ チャットはログイン後にご利用可能です
        </small>
    </div>

</div>
@endsection
