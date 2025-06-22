@if (session('message'))
    <div class="alert alert-success">{{ session('message') }}</div>
@endif

<a href="{{ route('stripe.onboarding') }}" class="btn btn-primary">
    Stripe連携を開始
</a>
