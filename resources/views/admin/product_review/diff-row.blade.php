<div class="diff-row diff-changed">

    <div style="margin-bottom:6px;">
        <strong>{{ $fieldLabels[$field] ?? $field }}</strong>
        <span style="color:#f39c12; font-size:12px;">変更あり</span>
    </div>

    <div class="diff-grid">

        {{-- Before --}}
        <div class="diff-box diff-before">

            <small>Before</small>

            @include('components.diff-value', [
                'value' => $change['before'] ?? null
            ])

        </div>

        {{-- After --}}
        <div class="diff-box diff-after">

            <small>After</small>

            @include('components.diff-value', [
                'value' => $change['after'] ?? null
            ])

        </div>

    </div>

</div>