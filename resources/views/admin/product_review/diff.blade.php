<div class="review-card">

    <div class="review-title">🔍 変更差分</div>

    @php
        $priorityFields = ['name','price','cover_img','cover_img2','cover_img3','movie'];
    @endphp

    {{-- 優先項目 --}}
    @foreach($priorityFields as $p)

        @if(isset($diff[$p]))

            @php $change = $diff[$p]; @endphp

            @if(($change['before'] ?? null) != ($change['after'] ?? null))

                @include('admin.product_review.partials.diff-row', [
                    'field' => $p,
                    'change' => $change
                ])

            @endif
        @endif

    @endforeach

    {{-- その他 --}}
    @foreach($diff as $field => $change)

        @if(in_array($field, $priorityFields))
            @continue
        @endif

        @if(($change['before'] ?? null) == ($change['after'] ?? null))
            @continue
        @endif

        @include('admin.product_review.partials.diff-row', [
            'field' => $field,
            'change' => $change
        ])

    @endforeach

</div>