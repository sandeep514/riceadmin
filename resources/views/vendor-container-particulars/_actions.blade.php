@if((int) $model->status === \App\VendorContainerParticular::STATUS_ACTIVE)
    <a href="{{ route('vendor-container-particular.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this container particular as inactive?');">Inactive</a>
@else
    <a href="{{ route('vendor-container-particular.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.vendor-container-particular', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.vendor-container-particular',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
