@php
    $status = (int) ($product->status ?? 0);
@endphp
@if($status === 1)
    <span class="label label-success">Verified / Active</span>
@elseif($status === 2)
    <span class="label label-info">Awaiting vendor</span>
    @if(!empty($product->admin_message))
        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($product->admin_message, 80) }}</small>
    @endif
@else
    <span class="label label-warning">Pending review</span>
@endif
