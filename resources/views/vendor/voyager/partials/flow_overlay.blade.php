@php
    use App\Models\Flow;

    $flow = Flow::with('steps')
        ->where('slug', $flowSlug ?? 'product_registration_flow')
        ->first();
@endphp

@if($flow)
<div id="flowOverlay" class="flow-overlay hidden">
    <h4>{{ $flow->title }}</h4>
    <ul id="flowSteps"></ul>
</div>
@endif
