{{-- Voyager 標準の browse を継承 --}}
@extends('voyager::bread.browse')

@php
    use App\Models\Flow;

    /**
     * 表示したい Flow を取得
     * ※ まずは固定 slug（あとで切替可能）
     */
    $flow = Flow::with(['steps' => function ($q) {
        $q->orderBy('step_order');
    }])->where('slug', 'product_registration_flow')->first();
@endphp

@section('content')
    {{-- 既存の Voyager index をそのまま表示 --}}
    @parent

    {{-- Flow Overlay --}}
    @if($flow)
        <div id="flowOverlay" class="flow-overlay hidden">
            <div class="flow-header">
                <h4 id="flowTitle">{{ $flow->title }}</h4>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="document.getElementById('flowOverlay').classList.add('hidden')">
                    ✕
                </button>
            </div>

            <p class="text-muted small">{{ $flow->overview }}</p>

            <ul id="flowSteps" class="flow-steps"></ul>
        </div>
    @endif
@endsection

{{-- ================= CSS ================= --}}
@push('css')
<style>
.flow-overlay {
    position: fixed;
    top: 80px;
    right: 24px;
    width: 420px;
    max-height: 80vh;
    overflow-y: auto;
    background: #ffffff;
    border-radius: 10px;
    padding: 16px;
    z-index: 9999;
    box-shadow: 0 10px 30px rgba(0,0,0,.2);
}

.flow-overlay.hidden {
    display: none;
}

.flow-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.flow-steps {
    list-style: none;
    padding: 0;
    margin-top: 12px;
}

.flow-step {
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
    font-size: 14px;
}

.flow-step.required {
    border-left: 5px solid #dc3545;
    background: #fff5f5;
}

.flow-step.optional {
    border-left: 5px solid #0dcaf0;
    background: #f5fbff;
}

.flow-step .badge {
    font-size: 11px;
}
</style>
@endpush

{{-- ================= JS ================= --}}
@push('javascript')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const flow = @json($flow);

    if (!flow || !flow.steps) return;

    const list = document.getElementById('flowSteps');
    list.innerHTML = '';

    flow.steps.forEach(step => {

        const li = document.createElement('li');
        li.className = 'flow-step ' + (step.is_required ? 'required' : 'optional');

        li.innerHTML = `
            <div>
                <strong>STEP ${step.step_order}: ${step.title}</strong>
                <span class="badge bg-${step.is_required ? 'danger' : 'secondary'} ms-2">
                    ${step.is_required ? '必須' : '任意'}
                </span>
            </div>
            ${step.description ? `<div class="mt-1">${step.description}</div>` : ''}
        `;

        list.appendChild(li);
    });

    // 初期表示（常に出したくない場合はここを消す）
    document.getElementById('flowOverlay').classList.remove('hidden');
});
</script>
@endpush
