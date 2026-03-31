@extends('layouts.seller')

@section('content')

<style>
    .dashboard-card{
        background:#949391;
        border-radius:12px;
        padding:16px;
        box-shadow:0 3px 12px rgba(0,0,0,0.05);
        margin-bottom:20px;
    }

    .status-badge{
        padding:4px 10px;
        border-radius:20px;
        font-size:12px;
        font-weight:600;
    }

    .status-pending{ background:#ffeaa7; }
    .status-reviewing{ background:#74b9ff; color:white;}
    .status-approved{ background:#55efc4; }
    .status-rejected{ background:#ff7675; color:white;}

    .product-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:12px;
        border-bottom:1px solid #eee;
    }

    .product-info{
        display:flex;
        gap:12px;
        align-items:center;
    }

    .product-img{
        width:60px;
        height:60px;
        border-radius:8px;
        object-fit:cover;
    }

    .app_product-img{
        width:60px;
        height:60px;
        border-radius:8px;
        object-fit:cover;
    }

    #approvedTable {
        transition: all 0.3s;
    }
    .toggle-btn {
        cursor: pointer;
    }

</style>

<div class="container">

    <h2>📦 出品者ダッシュボード</h2>

    {{-- 使用回数 --}}
    <div class="dashboard-card">
        本日の審査依頼：{{ $todayCount }} / 5
    </div>

    {{-- 商品一覧 --}}
    <div class="dashboard-card">

        {{-- 折りたたみテーブル例 --}}
        <div class="table-wrapper">
            <button class="toggle-btn btn btn-primary mb-2" type="button">
                承認済商品を表示 / 非表示
            </button>

            <table class="table table-bordered" id="approvedTable">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>画像</th>
                        <th>承認ステータス</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approved_products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>
                            <img src="{{ asset('storage/'.$product->cover_img) }}" alt="{{ $product->name }}" style="width:80px;">
                        </td>
                        <td>承認済</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @foreach($products as $product)

            <div class="product-row">

                {{-- 左 --}}
                <div class="product-info">

                    <img src="{{ asset('storage/'.$product->cover_img) }}"
                         class="product-img">

                    <div>
                        <strong>{{ $product->name }}</strong><br>
                        <strong>{{$product->review_comment}}</strong><br>
                        <strong>{{$product->review_comment}}</strong><br>

                        {{-- ステータス --}}
                        @if($product->review_status == 'pending')
                            <span class="status-badge status-pending">審査待ち</span>

                        @elseif($product->review_status == 'reviewing')
                            <span class="status-badge status-reviewing">審査中</span>

                        @elseif($product->review_status == 'rejected')
                            <span class="status-badge status-rejected">却下</span>

                        @elseif($product->review_status == 'need_fix')
                            <span class="status-badge" style="background:#e74c3c;color:#fff;">
                                修正依頼
                            </span>   

                        @else
                            <span class="status-badge">未申請</span>
                        @endif

                    </div>

                </div>

                <div>
                    
                </div>

                {{-- 右（操作） --}}
                <div>
                    審査依頼日：{{ optional($product->reviewQueue)->requested_at }}
                    {{-- 審査依頼 --}}
                    @if(in_array($product->review_status,['rejected', 'pending']))
                        <form method="POST"
                              action="{{ route('product.request', $product->id) }}"
                              style="display:inline;">
                            @csrf
                            <button class="btn btn-warning btn-sm">
                                審査依頼
                            </button>
                        </form>

                    @elseif($product->review_status == 'need_fix')
                        <a href="{{ route('seller.product.fix', $product->id) }}"
                           class="btn btn-danger btn-sm">
                            修正する
                        </a>
                    @endif

                    {{-- 編集 --}}
                    <a href="{{ route('voyager.products.edit', $product->id) }}"
                       class="btn btn-primary btn-sm">
                       編集
                    </a>


                </div>

            </div>

            {{-- 却下理由 --}}
            @if($product->review_status == 'rejected')
            
                <div style="padding:10px; background:turquoise; margin-bottom:10px;">
                    ❌ 却下理由：{{ $product->review_comment ?? '理由なし' }}
                </div>
            @endif

        @endforeach

    </div>

</div>

{{-- JSで折りたたみ --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.querySelector('.toggle-btn');
        const table = document.getElementById('approvedTable');

        toggleBtn.addEventListener('click', function () {
            if (table.style.display === 'none') {
                table.style.display = 'table';
            } else {
                table.style.display = 'none';
            }
        });
    });
</script>

@endsection