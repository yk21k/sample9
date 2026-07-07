@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">
        owner承認待ち商品
    </h2>

    @foreach($drafts as $draft)

        @php
            $product = $draft->product;
        @endphp

        <div class="card mb-3">

            <div class="card-body">

                <h5>
                    {{ $draft->name }}
                </h5>

                <div class="text-muted mb-2">
                    Draft ID: {{ $draft->id }}
                </div>

                @if($product)

                    <div class="text-muted mb-2">
                        Product ID: {{ $product->id }}
                    </div>

                @endif

                <div class="mb-3">

                    <span class="badge bg-warning">
                        owner確認待ち
                    </span>

                </div>

                {{-- 編集確認 --}}
                <a
                    href="{{ route('seller.product_edit_drafts.edit', $draft) }}"
                    class="btn btn-secondary"
                >
                    編集確認
                </a>

                {{-- 承認 --}}
                <form
                    method="POST"
                    action="{{ route('seller.product_edit_drafts.approve', $draft) }}"
                    class="d-inline"
                >
                    @csrf

                    <button
                        class="btn btn-success"
                        onclick="return confirm('承認しますか？')"
                    >
                        運営審査へ送信
                    </button>

                </form>

                {{-- 差し戻し --}}
                <form
                    method="POST"
                    action="{{ route('seller.product_edit_drafts.reject', $draft) }}"
                    class="mt-3"
                >
                    @csrf

                    <textarea
                        name="owner_note"
                        class="form-control mb-2"
                        placeholder="差し戻し理由"
                        required
                    ></textarea>

                    <button
                        class="btn btn-danger"
                        onclick="return confirm('差し戻しますか？')"
                    >
                        差し戻し
                    </button>

                </form>

            </div>

        </div>

    @endforeach

    {{ $drafts->links() }}

</div>

@endsection