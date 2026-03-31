@extends('voyager::master')

@section('content')

<style>
    {{-- メイン --}}
    .review-card{
        background:#fff;
        border-radius:10px;
        padding:16px;
        margin-bottom:16px;
        box-shadow:0 2px 10px rgba(0,0,0,0.05);
    }

    .review-title{
        font-weight:600;
        margin-bottom:12px;
    }

    .diff-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:12px;
    }

    .diff-box{
        padding:10px;
        border-radius:8px;
        min-height:80px;
    }

    .diff-before{
        background:#fff5f5;
        border-left:4px solid #e74c3c;
    }

    .diff-after{
        background:#f0fff4;
        border-left:4px solid #27ae60;
    }

    .diff-changed{
        border:2px solid #f39c12;
        padding:10px;
        border-radius:10px;
    }

    .media-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill,160px);
        gap:10px;
    }

    .media-grid video{
        width:160px;
        border-radius:8px;
    }

    .diff-changed{
        background:#fffdf5;
    }

    .diff-before{
        opacity:0.6;
    }

    .diff-after{
        font-weight:600;
    }

    .diff-row:hover{
        background:#f8fbff;
    }

    .media-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill,120px);
        gap:10px;
    }

    .review-image,
    .review-video{
        width:120px;
        height:120px;
        object-fit:cover;
        border-radius:8px;
        cursor:pointer;
    }

    {{-- キーボード操作 --}}
    .keyboard-guide{
        font-size:13px;
        color:#666;
    }

    .key-row{
        margin-top:8px;
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        align-items:center;
    }

    .key{
        display:inline-block;
        background:#f1f3f5;
        border:1px solid #ddd;
        border-radius:6px;
        padding:2px 6px;
        font-weight:600;
        font-size:12px;
    }

    {{-- 承認 / 却下 / 次の商品 --}}
    .review-action-bar{
        position:fixed;
        bottom:0;
        left:0;
        width:100%;
        background:white;
        border-top:1px solid #eee;
        padding:15px;
        display:flex;
        justify-content:center;
        gap:20px;
        z-index:999;
        box-shadow:0 -2px 10px rgba(0,0,0,0.05);
    }

    .review-action-bar .btn{
        min-width:160px;
        font-size:16px;
    }

    {{-- 情報 マニュアル --}}
    .manual-links{
        display:flex;
        flex-direction:column;
        gap:10px;
    }

    .manual-link{
        display:block;
        padding:10px;
        border-radius:8px;
        background:#f7f9fc;
        text-decoration:none;
        color:#333;
        transition:0.2s;
    }

    .manual-link:hover{
        background:#eaf1ff;
    }

</style>

{{-- 成功メッセージ --}}
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

{{-- エラーメッセージ（却下など） --}}
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

{{-- 情報 / マニュアル --}}
<div class="panel panel-bordered seller-panel">

    <div class="panel-heading">
        📘 審査ガイド
    </div>

    <div class="panel-body manual-links">

        <a href="/admin/manual/rules" target="_blank" class="manual-link">
            📜 禁止商品ルール
        </a>

        <a href="/admin/manual/ng-examples" target="_blank" class="manual-link">
            ❌ NG事例集
        </a>

        <a href="/admin/manual/ok-examples" target="_blank" class="manual-link">
            ✅ OK事例集
        </a>

        <a href="/admin/manual/review-flow" target="_blank" class="manual-link">
            🔁 審査フロー
        </a>

    </div>

</div>

{{-- 状態表示 --}}
<div class="review-status">
    状態：
    @if($queue->status == 'pending')
        <span class="label label-warning">未審査</span>
    @elseif($queue->status == 'reviewing')
        <span class="label label-info">審査中</span>
    @elseif($queue->status == 'approved')
        <span class="label label-success">承認済み</span>
    @endif
</div>

@if($queue->status === 'reviewing' && $queue->review_started_at)

<div class="panel panel-warning">
    <div class="panel-body">

        <strong>審査中</strong>

        @if($remainingSeconds !== null)
            <span id="review-timer" style="margin-left:10px;">
                残り時間：--:--
            </span>
        @endif

    </div>
</div>

@endif

@if($queue->status === 'reviewing' && $reviewer)

<div class="alert alert-info">
    👀 現在の審査担当：
    <strong>{{ $reviewer->name }}</strong>
</div>

@endif


{{-- 承認 / 却下 / 次の商品 --}}
<div class="review-action-bar">

    AIスコア：{{ $product->reviewQueue->ai_score }}
    
    <form method="POST" action="{{ route('product.approve',$product->id) }}">
        @csrf
        <button id="approveBtn" class="btn btn-success">
            ✔ 承認 (A)
        </button>
    </form>

    <form method="POST" action="{{ route('product.reject', $product->id) }}">
        @csrf

        {{-- 却下理由 --}}
        <div class="mb-2">
            <label for="review_comment" class="form-label">却下理由</label>
            <textarea name="review_comment" id="review_comment" class="form-control" rows="2" required placeholder="理由を入力してください"></textarea>
        </div>

        <button id="rejectBtn" class="btn btn-danger">
            ✖ 却下 (R)
        </button>
    </form>

    <form method="POST" action="{{ route('product.review.fix', $product->id) }}">
    @csrf

        {{-- 🔥 ここに置く --}}
        <input type="hidden" name="fix_fields" id="fixFieldsInput">

        {{-- コメント --}}
        <textarea name="review_comment" class="form-control"></textarea>

        <br>

        <button type="submit" name="action" value="fix" class="btn btn-warning">
            修正依頼
        </button>

    </form>

    <a href="{{ route('admin.product-review.next') }}" id="nextBtn" class="btn btn-primary">
        → 次へ (N)
    </a>

</div>

{{-- ⚠ 違反履歴 --}}
<div class="panel panel-default">

    <div class="panel-heading" style="cursor:pointer" onclick="toggleViolation()">
        ⚠ 違反履歴（{{ $violations->count() }}件）
    </div>

    <div id="violationBody" style="display:none">

        <div class="panel-body">

            @if($violations->count()==0)
                <p class="text-success">違反履歴なし</p>
            @else

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>タイプ</th>
                            <th>内容</th>
                            <th>レベル</th>
                            <th>日時</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($violations as $v)
                        <tr>
                            <td>{{ $v->violation_type }}</td>
                            <td>{{ $v->reason }}</td>
                            <td>
                                @if($v->severity==3)
                                    <span class="label label-danger">重大</span>
                                @elseif($v->severity==2)
                                    <span class="label label-warning">注意</span>
                                @else
                                    <span class="label label-info">軽微</span>
                                @endif
                            </td>
                            <td>{{ $v->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            @endif

        </div>

    </div>

</div>

{{-- メイン --}}
<div class="container-fluid">

    {{-- 差分 --}}
    @include('admin.product_review.partials.diff')

</div>

{{-- キーボード操作 --}}
<div class="panel panel-default">
    <div class="panel-body keyboard-guide">

        <strong>⌨ ショートカット</strong>

        <div class="key-row">
            <span class="key">A</span> 承認
            <span class="key">R">R</span> 却下
            <span class="key">N</span> 次へ
            <span class="key">←</span> 前画像
            <span class="key">→</span> 次画像
        </div>

    </div>
</div>

{{-- 画像と動画モーダル --}}
<div id="mediaModal" class="image-modal">
    <span class="close-btn">&times;</span>

    <div class="modal-inner">
        <img id="modalImage" class="modal-content" style="display:none;">
        <video id="modalVideo" class="modal-content" controls style="display:none;"></video>
    </div>

    <div class="nav prev">&#10094;</div>
    <div class="nav next">&#10095;</div>
</div>

{{-- 承認 / 却下 / 次の商品 --}}
<script>
    function toggleViolation(){
        const el = document.getElementById('violationBody');
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }

    document.getElementById('approveBtn').onclick = function(e){
        if(!confirm('承認しますか？')) e.preventDefault();
    };

    document.getElementById('rejectBtn').onclick = function(e){
        if(!confirm('却下しますか？')) e.preventDefault();
    };
</script>

{{-- 画像と動画モーダル --}}
<script>
    let mediaList = [];
    let currentIndex = 0;

    // 初期化
    document.querySelectorAll('.review-image, .review-video').forEach((el, index) => {

        mediaList.push({
            type: el.tagName.toLowerCase() === 'video' ? 'video' : 'image',
            src: el.tagName.toLowerCase() === 'video'
                ? el.querySelector('source')?.src || el.src
                : el.src
        });

        el.dataset.index = index;

        el.addEventListener('click', () => {
            currentIndex = index;
            openModal();
        });
    });

    function openModal(){
        document.getElementById('mediaModal').style.display = 'block';
        showMedia();
    }

    function showMedia(){

        const media = mediaList[currentIndex];

        const img = document.getElementById('modalImage');
        const video = document.getElementById('modalVideo');

        img.style.display = 'none';
        video.style.display = 'none';

        if(media.type === 'image'){
            img.src = media.src;
            img.style.display = 'block';
        }else{
            video.src = media.src;
            video.style.display = 'block';
        }
    }

    function nextMedia(){
        currentIndex = (currentIndex + 1) % mediaList.length;
        showMedia();
    }

    function prevMedia(){
        currentIndex = (currentIndex - 1 + mediaList.length) % mediaList.length;
        showMedia();
    }

    // ボタン操作
    document.querySelector('.next').onclick = nextMedia;
    document.querySelector('.prev').onclick = prevMedia;

    // 閉じる
    document.querySelector('.close-btn').onclick = () => {
        document.getElementById('mediaModal').style.display = 'none';
    };
</script>

{{-- キーボード操作 --}}
<script>
    document.addEventListener('keydown', function(e){

        // 入力中は無効
        const tag = document.activeElement.tagName;
        if(tag === "INPUT" || tag === "TEXTAREA") return;

        switch(e.key.toLowerCase()){

            case 'a':
                document.getElementById('approveBtn')?.click();
            break;

            case 'r':
                document.getElementById('rejectBtn')?.click();
            break;

            case 'n':
                document.getElementById('nextBtn')?.click();
            break;

            case 'arrowright':
                nextMedia();
            break;

            case 'arrowleft':
                prevMedia();
            break;

            case 'escape':
                document.getElementById('mediaModal').style.display = 'none';
            break;
        }

    });
</script>

<script>
    let seconds = {{ $remainingSeconds ?? 0 }};

    function updateTimer(){

        if(seconds <= 0){
            document.getElementById('review-timer').innerText = "解除可能";
            return;
        }

        let m = Math.floor(seconds / 60);
        let s = seconds % 60;

        document.getElementById('review-timer').innerText =
            `残り時間：${m}:${s.toString().padStart(2,'0')}`;

        seconds--;
    }

    setInterval(updateTimer,1000);
    updateTimer();
</script>

{{-- （リアルタイムカウント） --}}

<script>
    function updateTimers(){

        document.querySelectorAll('.review-timer').forEach(el => {

            const start = new Date(el.dataset.start);
            const limit = parseInt(el.dataset.limit); // 分

            const now = new Date();

            const diff = (now - start) / 1000; // 秒
            const remain = (limit * 60) - diff;

            if(remain <= 0){
                el.innerHTML = '⚠️ 期限切れ';
                el.style.color = 'red';
                return;
            }

            const m = Math.floor(remain / 60);
            const s = Math.floor(remain % 60);

            el.innerHTML = `⏱ ${m}:${s.toString().padStart(2,'0')}`;
        });
    }

    // 1秒ごと更新
    setInterval(updateTimers, 1000);
    updateTimers();
</script>

{{-- 修正対象クリック制御 --}}
<script>

    let fixFields = new Set(@json($fixFields ?? []));

    function toggleFix(el, event){

        // 子要素クリック無視（重要）
        if(event.target.tagName === 'VIDEO' || event.target.tagName === 'IMG'){
            return;
        }

        const field = el.dataset.field;

        if(fixFields.has(field)){
            fixFields.delete(field);
            el.classList.remove('fix-target');
        }else{
            fixFields.add(field);
            el.classList.add('fix-target');
        }

        console.log([...fixFields]);

        updateFixInput();
    }

    function updateFixInput(){
        const input = document.getElementById('fixFieldsInput');
        if(input){
            input.value = JSON.stringify(Array.from(fixFields));
        }
    }

    // 初期化
    document.addEventListener('DOMContentLoaded', function(){
        updateFixInput();
    });

</script>

{{-- typeof toggleFix --}}
<script>
    document.addEventListener('DOMContentLoaded', function(){

        console.log('JS loaded');

        document.querySelector('form').addEventListener('submit', function(e){

            if(fixFields.size === 0){
                alert('修正箇所を選択してください');
                e.preventDefault();
            }

        });

        window.fixFields = new Set();

        document.addEventListener('click', function(e){

            const el = e.target.closest('[data-field]');
            if(!el) return;

            console.log('clicked'); // ←これが出るかだけ見る

            const field = el.dataset.field;

            if(fixFields.has(field)){
                fixFields.delete(field);
                el.classList.remove('fix-target');
            }else{
                fixFields.add(field);
                el.classList.add('fix-target');
            }

            document.getElementById('fixFieldsInput').value =
                JSON.stringify([...fixFields]);

            console.log([...fixFields]);
        });

    });
</script>

@endsection