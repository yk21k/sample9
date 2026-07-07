@php
    use Illuminate\Support\Facades\Storage;
@endphp

@if(is_null($value))
    <span class="diff-empty">（なし）</span>

@elseif(is_array($value))

    {{-- 配列（画像配列など） --}}
    <div class="image-box">
        @foreach($value as $v)
            @if(Str::contains($v, ['.jpg','.png','.jpeg','.webp']))
                <img src="{{ Storage::url($v) }}">
            @else
                <div>{{ $v }}</div>
            @endif
        @endforeach
    </div>

@elseif(Str::contains($value, ['.jpg','.png','.jpeg','.webp']))

    {{-- 画像 --}}
    <img src="{{ Storage::url($value) }}">

@else

    {{-- 通常文字 --}}
    {{ $value }}

@endif