@if((int) $model->status === \App\VendorCurrency::STATUS_ACTIVE)
    <a href="{{ route('vendor-currency.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this Currency as inactive?');">Inactive</a>
@else
    <a href="{{ route('vendor-currency.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
