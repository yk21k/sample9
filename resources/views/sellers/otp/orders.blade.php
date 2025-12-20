<td>
    @if ($order->status === 'awaiting_handover')
        <span class="badge bg-warning">受け取り準備中</span>
    @elseif ($order->status === 'ready_for_handover')
        <span class="badge bg-success">受け渡し可</span>
    @else
        <span class="badge bg-secondary">{{ $order->status }}</span>
    @endif
</td>
@if($order->status === 'paid')
    <a href="{{ route('pickup.otp.generate', ['order' => $order->id]) }}" 
       class="btn btn-outline-primary">
        ワンタイムパスワードを発行
    </a>
@endif
