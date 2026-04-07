<style>
    .fix-target {
        border: 2px solid #e74c3c;
        background: #fdecea;
        padding: 10px;
        border-radius: 6px;
    }

    /* さらに強調 */
    .fix-target::before {
        content: "修正対象";
        display: inline-block;
        background: #e74c3c;
        color: #fff;
        font-size: 10px;
        padding: 2px 6px;
        margin-bottom: 6px;
        border-radius: 3px;
    }
</style>

 @php
    $isFixTarget = in_array($field, $fixFields ?? []);

    $before = $change['before'] ?? null;
    $after  = $change['after'] ?? null;

    $isChanged = $before != $after;
@endphp

<pre>{{ print_r($fixFields, true) }}</pre>

<div class="diff-row {{ $isFixTarget ? 'fix-target' : '' }}" data-field="{{ $field }}">

    <div style="margin-bottom:6px;">
        <strong>{{ $fieldLabels[$field] ?? $field }}</strong>

        {{-- 修正対象 --}}
        @if($isFixTarget)
            <span class="badge badge-danger">修正対象</span>
        @endif

        {{-- 未修正 --}}
        @if($isFixTarget && !$isChanged)
            <span class="badge badge-warning">未修正</span>
        @endif

        <span style="color:#f39c12; font-size:12px;">変更あり</span>
    </div>

    <div class="diff-grid">

        {{-- Before --}}
        <div class="diff-box diff-before">
            <small>Before</small>

            @include('components.products_diff.diff-value', [
                'value' => $before,
                'type' => 'before'
            ])
        </div>

        {{-- After --}}
        <div class="diff-box diff-after">
            <small>After</small>

            @include('components.products_diff.diff-value', [
                'value' => $after,
                'type' => 'after'
            ])
        </div>

    </div>

</div>

