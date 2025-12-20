@extends('layouts.seller')

@section('title', '受け取り枠の編集')

@section('content')

<div class="container py-5">
    <div class="card shadow-sm rounded-3 bg-secondary">
        <div class="card-header bg-secondary">
            <h4 class="mb-0">受け取り枠の編集</h4>
        </div>
        {{-- ✅ フラッシュメッセージの表示 --}}
		@if(session('success'))
		    <div class="alert alert-success">
		        {{ session('success') }}
		    </div>
		@endif

        <div class="card-body">
            {{-- エラー表示 --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- フォーム開始 --}}
            <form method="POST" action="{{ route('seller.pickup.slots.update', $pickupSlot->id) }}" class="bg-secondary">
                @csrf
                @method('PUT')

                {{-- 日付 --}}
                <div class="mb-3">
                    <label for="date" class="form-label">受け取り日</label>
                    <input type="date" name="date" id="date"
                           value="{{ old('date', $pickupSlot->date) }}"
                           class="form-control" required>
                </div>

                {{-- 開始時間 --}}
                <div class="mb-3">
                    <label for="start_time" class="form-label">開始時間</label>
                    <input type="time" name="start_time" id="start_time"
                           value="{{ old('start_time', $pickupSlot->start_time) }}"
                           class="form-control" required>
                </div>

                {{-- 終了時間 --}}
                <div class="mb-3">
                    <label for="end_time" class="form-label">終了時間</label>
                    <input type="time" name="end_time" id="end_time"
                           value="{{ old('end_time', $pickupSlot->end_time) }}"
                           class="form-control" required>
                </div>

                {{-- 容量 --}}
                <div class="mb-3">
                    <label for="capacity" class="form-label">定員（最大人数）</label>
                    <input type="number" name="capacity" id="capacity"
                           value="{{ old('capacity', $pickupSlot->capacity) }}"
                           class="form-control" min="1" required>
                </div>
                {{-- 残り枠 --}}
                <div class="mb-3">
                    <label for="remaining_capacity" class="form-label">残り枠</label>
                    <input type="number" name="remaining_capacity" id="remaining_capacity"
                           value="{{ old('remaining_capacity', $pickupSlot->remaining_capacity) }}"
                           class="form-control" min="0" required>
                </div>

                {{-- 備考 --}}
                <div class="mb-3">
                    <label for="note" class="form-label">備考</label>
                    <textarea name="note" id="note" rows="3" class="form-control"
                              placeholder="特記事項など">{{ old('note', $pickupSlot->note) }}</textarea>
                </div>

                {{-- 登録ボタン --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('seller.pickup.slots.index') }}" class="btn btn-secondary">
                        戻る
                    </a>
                    <button type="submit" class="btn btn-primary">
                        更新する
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
