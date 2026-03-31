

@php
use Illuminate\Support\Str;

$decoded = is_string($value) ? json_decode($value, true) : null;
$isJson = is_array($decoded);

$isBefore = ($diffType ?? '') === 'before';

$basePath = $isBefore
    ? 'storage/versions/'
    : 'storage/';
@endphp


{{-- null --}}
@if(empty($value))
    <span>-</span>

{{-- JSON --}}
@elseif($isJson)

    <div class="media-grid">

        @foreach($decoded as $item)

            {{-- 動画 --}}
            @if(is_array($item) && isset($item['download_link']))
                <video class="review-video" controls>
                    <source src="{{ asset($basePath.$item['download_link']) }}">
                </video>

            {{-- 画像 --}}
            @elseif(is_string($item))
                <img src="{{ asset($basePath.$item) }}"
                     class="review-image">
            @endif

        @endforeach

    </div>

{{-- 改行区切り画像 --}}
@elseif(is_string($value) && str_contains($value, "\n"))

    <div class="media-grid">

        @foreach(explode("\n", $value) as $line)

            @php $line = trim($line); @endphp

            @if(!empty($line))

                @if(Str::endsWith($line, ['.jpg','.jpeg','.png','.webp']))
                    <img src="{{ asset($basePath.$line) }}"
                         class="review-image">

                @elseif(Str::endsWith($line, ['.mp4','.mov','.webm']))
                    <video class="review-video" controls>
                        <source src="{{ asset($basePath.$line) }}">
                    </video>
                @endif

            @endif

        @endforeach

    </div>    

{{-- 配列（JSONじゃない） --}}
@elseif(is_array($value))

    <pre style="font-size:12px;">
{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}
    </pre>

{{-- 単体画像 --}}
@elseif(is_string($value) && Str::endsWith($value, ['.jpg','.jpeg','.png','.webp']))
    <img src="{{ asset($basePath.$value) }}"
         class="review-image">

{{-- 単体動画 --}}
@elseif(is_string($value) && Str::endsWith($value, ['.mp4','.mov','.webm']))
    <video class="review-video" controls>
        <source src="{{ asset($basePath.$value) }}">
    </video>

{{-- テキスト --}}
@else
    <div style="white-space: pre-line;">
        {{ (string)$value }}
    </div>
@endif