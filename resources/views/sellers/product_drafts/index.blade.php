@extends('layouts.seller')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            商品申請一覧
        </h2>



    </div>

    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif

    <div class="card">

        <div class="card-body p-0">

            <table class="table table-hover mb-0">

                <thead class="table-light">

                    <tr>

                        <th width="120">
                            画像
                        </th>

                        <th>
                            商品名
                        </th>

                        <th width="140">
                            価格
                        </th>

                        <th width="140">
                            在庫
                        </th>

                        <th width="180">
                            ステータス
                        </th>

                        <th width="200">
                            作成日
                        </th>

                        <th width="140">
                            操作
                        </th>

                        <th width="140">
                            削除
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($drafts as $draft)

                        <tr>

                            {{-- 画像 --}}
                            <td>
                                @if($draft->cover_img)

                                    
                                    <img
                                        src="{{ Storage::disk('s3')->url($draft->cover_img) }}"
                                        style="width:120px;border-radius:12px;"
                                    >

                                @else

                                    <div
                                        class="bg-light d-flex align-items-center justify-content-center"
                                        style="
                                            width:80px;
                                            height:80px;
                                            border-radius:12px;
                                        "
                                    >
                                        No Image
                                    </div>

                                @endif

                            </td>

                            {{-- 商品名 --}}
                            <td>

                                <div class="fw-bold">

                                    {{ $draft->name }}

                                </div>

                                @if($draft->owner_comment)

                                    <div class="text-danger small mt-1">

                                        差し戻し:
                                        {{ $draft->owner_comment }}

                                    </div>

                                @endif

                            </td>

                            {{-- 価格 --}}
                            <td>

                                ¥{{ number_format($draft->price) }}

                            </td>

                            {{-- 在庫 --}}
                            <td>

                                {{ $draft->stock }}

                            </td>

                            {{-- status --}}
                            <td>

                                @if($draft->status === 'owner_pending')

                                    <span class="badge bg-warning">
                                        owner承認待ち
                                    </span>
                                    @if(!($draft->product_id))

                                        <span class="badge bg-info">
                                            新規商品  
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            公開済みの商品の編集中  
                                        </span>    

                                    @endif

                                @elseif($draft->status === 'approved')

                                    <span class="badge bg-success">
                                        運営審査中
                                    </span>
                                    @if(!($draft->product_id))

                                        <span class="badge bg-info">
                                            新規商品  
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            公開済みの商品の編集中  
                                        </span>    

                                    @endif

                                @elseif($draft->status === 'rejected')

                                    <span class="badge bg-danger">
                                        差し戻し
                                    </span>
                                    @if(!($draft->product_id))

                                        <span class="badge bg-info">
                                            新規商品  
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            公開済みの商品の編集中  
                                        </span>    

                                    @endif

                                @else

                                    <span class="badge bg-secondary">
                                        {{ $draft->status }}
                                    </span>
                                    @if(!($draft->product_id))

                                        <span class="badge bg-info">
                                            新規商品  
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            公開済みの商品の編集中  
                                        </span>    

                                    @endif

                                @endif



                            </td>

                            {{-- 日付 --}}
                            <td>

                                {{ $draft->created_at }}

                            </td>

                            {{-- 操作 --}}
                            <td>

                                <a
                                    href="{{ route('seller.product_drafts.edit', $draft) }}"
                                    class="btn btn-sm btn-primary"
                                >
                                    詳細
                                </a>

                            </td>
                            
                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('seller.product_drafts.destroy', $draft) }}"
                                    class="d-inline"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger"
                                        onclick="return confirm('Draftを削除しますか？')"
                                    >
                                        削除
                                    </button>
                                </form>
                            </td>    
                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="text-center py-5">

                                商品申請はありません

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-4">

        {{ $drafts->links() }}

    </div>

</div>

@endsection