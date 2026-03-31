<div class="review-card">

    <div class="review-title">🔍 変更差分</div>

    @php
        $priorityFields = ['name','price','cover_img','cover_img2','cover_img3','movie'];

        $fieldLabels = [
            'name' => '商品名',
            'price' => '価格',
            'cover_img' => '画像1',
            'cover_img2' => '画像2',
            'cover_img3' => '画像3',
            'movie' => '動画',
        ];
    @endphp

    <table class="table table-bordered review-table">

        <thead>
            <tr>
                <th>項目</th>
                <th>変更前</th>
                <th>変更後</th>
            </tr>
        </thead>

        <tbody>

        {{-- 優先項目 --}}
        @foreach($priorityFields as $p)

            @if(isset($diff[$p]))

                @php $change = $diff[$p]; @endphp

                @if(($change['before'] ?? null) != ($change['after'] ?? null) || is_null($change['before']))

                    @include('admin.product_review.partials.diff-row', [
                        'field' => $p,
                        'label' => $fieldLabels[$p] ?? $p,
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

            @if(in_array($field, ['updated_at','created_at']))
                @continue
            @endif

            @if(($change['before'] ?? null) == ($change['after'] ?? null))
                @continue
            @endif

            @include('admin.product_review.partials.diff-row', [
                'field' => $field,
                'label' => $fieldLabels[$field] ?? $field,
                'change' => $change
            ])

        @endforeach

        </tbody>

    </table>

</div>