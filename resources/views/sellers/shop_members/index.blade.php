@extends('layouts.seller')

@section('content')

<style>
    
    .member-grid{
        display:grid;
        gap:20px;
    }

    .member-card{
        background:#fff;
        border-radius:12px;
        padding:20px;
        box-shadow:0 2px 8px rgba(0,0,0,0.08);
    }

    .member-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        margin-top:20px;
    }

    .status{
        padding:4px 10px;
        border-radius:999px;
        font-size:12px;
    }

    .status.active{
        background:#d4edda;
    }

    .status.pending{
        background:#fff3cd;
    }

    .status.expired{
        background:#f8d7da;
    }

</style>

<h2 class="mb-4">メンバー一覧</h2>

<a
    href="{{ route('shop_members.create', auth()->user()->shopMember->shop->id) }}"
    class="btn btn-primary mb-4"
>
    メンバー追加
</a>

<div class="member-grid">

@foreach($members as $member)

    <div class="member-card">

        {{-- ========================= --}}
        {{-- 基本情報 --}}
        {{-- ========================= --}}
        <div class="member-header text-muted">

            <div>
                <strong>
                    {{ $member->name }}
                </strong>

                <div class="small text-muted">
                    {{ $member->email ?? 'メール未設定' }}
                </div>
            </div>

            <div>
                <span class="role-badge">
                    {{ $member->role }}
                </span>
            </div>

        </div>

        {{-- ========================= --}}
        {{-- ステータス --}}
        {{-- ========================= --}}
        @php

            $expireAt = null;

            $expired = false;

            $remainText = null;

            if($member->invite_at){

                $expireAt = $member->invite_at
                    ->copy()
                    ->addDays(
                        config('invite.expire_days', 3)
                    );

                $expired = now()->gt($expireAt);

                if(!$expired){

                    $remainText = now()->diffForHumans(
                        $expireAt,
                        [
                            'parts' => 2,
                            'short' => true,
                            'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
                        ]
                    );
                }
            }

        @endphp

        <div class="mt-2">
            @if($member->user_id)

                <span class="badge bg-success">
                    登録済み
                </span>

            @elseif(!$expireAt)

                <span class="badge bg-secondary">
                    未招待
                </span>

            @elseif($expired)

                <span class="badge bg-danger">
                    期限切れ
                </span>

            @elseif(
                $expireAt &&
                $expireAt->diffInHours(now()) <= 24
            )

                <span class="badge bg-danger">
                    {{ $remainText }}
                </span>

            @else

                <span class="badge bg-warning text-dark">
                    {{ $remainText }}
                </span>

            @endif

        </div>

        {{-- ========================= --}}
        {{-- 操作 --}}
        {{-- ========================= --}}
        <div class="actions">

            {{-- 履歴 --}}
            <a
                href="{{ route('shop_members.logs', $member) }}"
                class="btn btn-light"
            >
                履歴
            </a>

            @if(!$member->user_id)

                {{-- 再招待 --}}
                {{-- ======================================== --}}
                {{-- 🔥 1分以内は再送禁止 --}}
                {{-- ======================================== --}}
                @if(
                    $member->invite_at &&
                    $member->invite_at->gt(now()->subMinute())
                )

                    @php
                        $remain = max(
                            0,
                            60 - now()->diffInSeconds($member->invite_at)
                        );
                    @endphp

                    <button
                        class="btn btn-secondary"
                        disabled
                    >
                        {{ $remain }}秒待機
                    </button>

                @else

                    {{-- 再招待 --}}
                    <form
                        method="POST"
                        action="{{ route('member.resend', $member) }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-info"

                            onclick="
                                if(!confirm('本当に再招待しますか？')){
                                    return false;
                                }

                                this.disabled=true;
                                this.innerText='再招待中...';

                                this.form.submit();
                            "
                        >
                            再招待
                        </button>

                    </form>

                @endif

                {{-- 失効 --}}
                <form
                    method="POST"
                    action="{{ route('shop_members.expire', $member) }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-warning"

                        onclick="
                            if(!confirm('本当に失効しますか？')){
                                return false;
                            }

                            this.disabled=true;
                            this.innerText='失効中...';

                            this.form.submit();
                        "
                    >
                        失効
                    </button>
                </form>

            @endif

            {{-- 削除 --}}
            @if($member->role !== 'owner')

                <form
                    method="POST"
                    action="{{ route('shop_members.destroy', $member) }}"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-danger"

                        onclick="
                            if(!confirm('本当に削除しますか？')){
                                return false;
                            }

                            this.disabled=true;
                            this.innerText='削除中...';

                            this.form.submit();
                        "
                    >
                        削除
                    </button>

                </form>

            @endif

        </div>

    </div>

@endforeach

</div>

@endsection