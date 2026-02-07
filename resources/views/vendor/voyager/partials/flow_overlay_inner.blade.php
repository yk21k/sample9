<div id="flowOverlay" class="flow-overlay hidden">

    <div class="flow-header">
        <h4 class="flow-title">{{ $flow->title }}</h4>

        <div class="flow-actions">
            <button
                type="button"
                class="btn btn-xs btn-outline-secondary"
                id="downloadFlowBtn"
                title="ダウンロード"
            >
                ⬇
            </button>

            <button
                type="button"
                class="btn btn-xs btn-outline-secondary"
                id="closeFlowBtn"
                title="閉じる"
            >
                ✕
            </button>
        </div>
    </div>


    @if($flow->overview)
        <p class="flow-overview">{{ $flow->overview }}</p>
    @endif

    <ul class="flow-steps">
        @foreach($flow->steps as $step)
            <li class="flow-step {{ $step->is_required ? 'required' : 'optional' }}">
                <div class="flow-step-title">
                    STEP {{ $step->step_order }}：{{ $step->title }}
                    <span class="badge {{ $step->is_required ? 'badge-danger' : 'badge-secondary' }}">
                        {{ $step->is_required ? '必須' : '任意' }}
                    </span>
                </div>

                @if($step->description)
                    <div class="flow-step-desc">
                        {{ $step->description }}
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>

{{-- 開くボタン --}}
<button
    type="button"
    id="openFlowBtn"
    class="btn btn-sm btn-primary flow-open-btn"
>
    📘 フローを見る
</button>
