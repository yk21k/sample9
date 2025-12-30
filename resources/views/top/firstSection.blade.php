@extends('layouts.public')

@section('content')
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="fw-bold">このサイトでできること</h1>
        <p class="text-muted mt-3">
            出店したい方、購入したい方、目的に応じてお選びください
            <br><small>画面右上のdarkモードをお勧めします</small>
        </p>
    </div>
    <h2 style="color:red">DEBUG: BEFORE SELLER</h2>

    <div class="row justify-content-center g-5">
        {{-- 出店者 --}}
        @if(isset($seller))
        <h3 style="color:green">SELLER EXISTS</h3>
        <div class="col-md-5 seller-panel">
            <div class="card h-100 shadow-sm" style="background-color: {{ $seller->bg_color ?? '#0f172a' }}">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-3">{{ $seller->title }}</h3>
                    <p class="text-muted">{{ $seller->description }}</p>

                    <ul class="text-start small mt-4">
                        @if(!empty($seller->features) && is_array($seller->features))
                            @foreach($seller->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        @endif
                    </ul>

                    @if($seller->cta_text && $seller->cta_url)
                        <a href="{{ $seller->cta_url }}" 
                           class="btn btn-primary mt-4 w-100"
                           style="background-color: {{ $seller->btn_color ?? '#2563eb' }}; border-color: {{ $seller->btn_color ?? '#2563eb' }}">
                            {{ $seller->cta_text }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- 購入者 --}}
        @if(isset($buyer))
        <h3 style="color:blue">BUYER EXISTS</h3>
        <div class="col-md-5 buyer-panel">
            <div class="card h-100 shadow-sm" style="background-color: {{ $buyer->bg_color ?? '#1e1b2e' }}">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-3">{{ $buyer->title }}</h3>
                    <p class="text-muted">{{ $buyer->description }}</p>

                    <ul class="text-start small mt-4">
                        @if(!empty($buyer->features) && is_array($buyer->features))
                            @foreach($buyer->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        @endif
                    </ul>

                    @if($buyer->cta_text && $buyer->cta_url)
                        <a href="{{ $buyer->cta_url }}" 
                           class="btn btn-outline-primary mt-4 w-100"
                           style="border-color: {{ $buyer->btn_color ?? '#6d28d9' }}; color: {{ $buyer->btn_color ?? '#6d28d9' }}">
                            {{ $buyer->cta_text }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- スキップ・補助導線 --}}
    <div class="text-center mt-5">
        <form method="POST" action="{{ route('entrance.pass') }}">
            @csrf
            <button class="btn btn-link text-muted">
                今回は説明を省略する
            </button>
        </form>
    </div>

</div>
@endsection
