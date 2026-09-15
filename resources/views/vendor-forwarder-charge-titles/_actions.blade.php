@if((int) $model->status === \App\VendorForwarderChargeTitle::STATUS_ACTIVE)
    <a href="{{ route('vendor-forwarder-charge-title.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this Forwarder charge title as inactive?');">Inactive</a>
@else
    <a href="{{ route('vendor-forwarder-charge-title.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.vendor-forwarder-charge-title', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.vendor-forwarder-charge-title',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
