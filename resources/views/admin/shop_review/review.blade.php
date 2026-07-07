@extends('voyager::master')

@section('content')

<style>
    .diff-table {
        width: 100%;
        border-collapse: collapse;
    }

    .diff-table th, .diff-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    .diff-header {
        background: #f5f5f5;
    }

    .diff-changed {
        background: #ffe0e0;
    }

    .diff-same {
        background: #f9f9f9;
    }

    .diff-empty {
        color: #999;
        font-style: italic;
    }

    .diff-changed img {
        border: 3px solid red;
    }

    .diff-same img {
        opacity: 0.6;
    }

    .image-box {
        display: flex;
        gap: 10px;
    }

    .image-box img {
        width: 120px;
        border-radius: 6px;
    }

    .preview-img {
        width: 120px;
        cursor: pointer;
        border-radius: 6px;
        transition: 0.2s;
    }

    .preview-img:hover {
        transform: scale(1.05);
    }

    /* モーダル */
    #imgModal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
    }

    #imgModal img {
        display: block;
        margin: auto;
        max-width: 90%;
        max-height: 90%;
        margin-top: 5%;
    }
</style>

<div class="container">

    <h2>出店者審査</h2>

    <p>
    申請ID: {{ $application->id }} <br>
    種別: {{ $application->type }} <br>
    ステータス: {{ $application->status }}
    </p>

    <table class="diff-table">
        <tr class="diff-header">
            <th style="width:20%">項目</th>
            <th style="width:40%">変更前</th>
            <th style="width:40%">変更後</th>
        </tr>

        @php
            $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        @endphp

        @foreach($keys as $key)

            @php
                $b = $before[$key] ?? null;
                $a = $after[$key] ?? null;

                $changed = $b != $a;
            @endphp

            <tr>
                <td>{{ $key }}</td>

                {{-- before --}}
                <td class="{{ $changed ? 'diff-changed' : 'diff-same' }}">
                    @include('admin.shop_applications.partials.value', ['value' => $b])
                </td>

                {{-- after --}}
                <td class="{{ $changed ? 'diff-changed' : 'diff-same' }}">
                    @include('admin.shop_applications.partials.value', ['value' => $a])
                </td>
            </tr>

        @endforeach

    </table>

    <div style="margin-top:20px; display:flex; gap:10px;">

        <form method="POST" action="/admin/shop-applications/{{ $application->id }}/approve">
            @csrf
            <textarea name="comment" placeholder="承認コメント"></textarea>
            <br>
            <button class="btn btn-success">承認</button>
        </form>

        <form method="POST" action="/admin/shop-applications/{{ $application->id }}/reject">
            @csrf
            <textarea name="reason" placeholder="却下理由"></textarea>
            <br>
            <button class="btn btn-danger">却下</button>
        </form>

    </div>

    <div id="imgModal" onclick="closeModal()">
        <img id="modalImg">
    </div>

</div>

<script>
    function openModal(src){
        document.getElementById('imgModal').style.display = 'block';
        document.getElementById('modalImg').src = src;
    }

    function closeModal(){
        document.getElementById('imgModal').style.display = 'none';
    }
</script>

@endsection