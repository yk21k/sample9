@extends('layouts.public')

@section('content')
<div class="container py-5">

    {{-- ページタイトル --}}
    <div class="text-center mb-5">
        <h1 class="fw-bold">出店について</h1>
        <p class="text-muted mt-3">
            このマーケットでできること、よくある質問をまとめています
        </p>
    </div>

    {{-- 概要 --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="fw-bold mb-3">出店はとてもシンプルです</h3>
                    <p class="text-muted">
                        商品を登録するだけで販売を始められます。
                        初期費用はかからず、管理画面から簡単に操作できます。
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <h3 class="fw-bold mb-4 text-center">Q＆A botに聞く例</h3>

            @foreach($faqs as $faq)
                <div class="mb-3">
                    <div class="fw-bold">

                        Q. {{ $faq->question }}
                        
                    </div>
                    <div class="text-muted mt-1">
                        A.　{!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- FAQ活用の説明 --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-8 text-center">
            <p class="text-muted">
                これらの質問は、ページだけでなく<br>
                チャットからも同じ内容を確認できます。
            </p>
        </div>
    </div>
    <div class="row justify-content-center mb-5">
        <div class="col-md-8 text-center">
            <h2>
                （Q&A）詳細な回答
            </h2>

            <p class="text-gray-600 mb-6">
                出店前によくあるご質問をまとめています。
                詳細はQ&Aページをご覧ください。
            </p>

            <a href="{{ route('seller.qanda.index') }}"
               class="inline-block px-6 py-3 rounded bg-blue-600 text-white hover:bg-blue-700">
                よくある質問一覧を見る
            </a>
        </div>
    </div>

    {{-- CTA --}}
    <div class="row justify-content-center">
        <div class="col-md-6">
            <a href="/seller/apply" class="btn btn-primary w-100 py-3">
                出店を始める
            </a>
        </div>
    </div>



</div>

@endsection
