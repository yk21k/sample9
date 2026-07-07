@extends('layouts.seller')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>商品一覧</h2>
        {{-- 新規商品 --}}
        @php

            $currentShop = auth()->user()->currentShop();

            $member = auth()->user()->shopMember;

        @endphp

        @if(
            in_array(
                $member->role,
                ['manager', 'owner']
            )
        )
            <a
                href="{{ route('seller.products.create') }}"
                class="btn btn-primary"
            >

                ＋ 商品追加
            </a>
        @endif    
    </div>

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif

    <div class="card">

        <div class="card-body p-0">

            <table class="table mb-0">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名</th>
                        <th>価格</th>
                        <th>公開状態</th>
                        <th>審査状態</th>
                        <th>編集</th>
                    </tr>
                </thead>

                <tbody>
                    
                    @forelse($products as $product)

                        <tr>

                            <td>
                                {{ $product->id }}
                            </td>

                            <td>
                                {{ $product->name }}
                            </td>

                            <td>
                                ¥{{ number_format($product->price) }}
                            </td>

                            <td>

                                @if(
                                    $product->status == 1 &&
                                    $product->review_status === 'approved'
                                )

                                    <span class="badge bg-success">
                                        公開中
                                    </span>

                                @elseif(
                                    $product->status == 0 &&
                                    $product->review_status === 'owner_pending'
                                )

                                    <span class="badge bg-warning">
                                        Owner承認待ち
                                    </span>

                                @elseif(
                                    $product->status == 0 &&
                                    $product->review_status === 'pending'
                                )

                                    <span class="badge bg-info">
                                        運営審査中
                                    </span>

                                @elseif(
                                    $product->status == 0 &&
                                    $product->review_status === 'rejected'
                                )

                                    <span class="badge bg-danger">
                                        差し戻し
                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        非公開
                                    </span>

                                @endif

                            </td>
                            <td>    
                                @if($product->review_status === 'approved')

                                    <span class="badge bg-success">
                                        承認済
                                    </span>

                                @elseif($product->review_status === 'pending')

                                    <span class="badge bg-warning text-dark">
                                        審査中
                                    </span>

                                @elseif($product->review_status === 'need_fix')

                                    <span class="badge bg-danger">
                                        修正依頼
                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        下書き
                                    </span>

                                @endif

                            </td>

                           <td>

                                @if(in_array($member->role, ['staff', 'manager', 'owner']) &&           in_array($product->review_status, ['approved', 'owner_pending'])
                                     
                                    )

                                    <form
                                        method="POST"
                                        action="{{ route('seller.products.edit_draft', $product) }}"
                                        class="d-inline"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-primary"
                                        >
                                            編集
                                        </button>
                                    </form>

                                @endif

                            </td>

                            

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center py-4">

                                商品がありません

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-4">

        {{ $products->links() }}

    </div>

</div>

@endsection