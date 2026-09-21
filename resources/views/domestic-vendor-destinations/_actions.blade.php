@if((int) $model->status === \App\DomesticVendorDestination::STATUS_ACTIVE)
    <a href="{{ route('domestic-vendor-destination.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this Destination as inactive?');">Inactive</a>
@else
    <a href="{{ route('domestic-vendor-destination.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.domestic-vendor-destination', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.domestic-vendor-destination',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
