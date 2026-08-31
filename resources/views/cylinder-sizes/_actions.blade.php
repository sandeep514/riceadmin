@if((int) $model->status === \App\CylinderSize::STATUS_ACTIVE)
    <a href="{{ route('cylinder-size.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this cylinder size as inactive?');">Inactive</a>
@else
    <a href="{{ route('cylinder-size.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.cylinder-size', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.cylinder-size',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
