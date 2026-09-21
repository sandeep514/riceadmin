@if((int) $model->status === \App\DomesticVendorTruckSize::STATUS_ACTIVE)
    <a href="{{ route('domestic-vendor-truck-size.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this Truck size as inactive?');">Inactive</a>
@else
    <a href="{{ route('domestic-vendor-truck-size.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.domestic-vendor-truck-size', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.domestic-vendor-truck-size',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
