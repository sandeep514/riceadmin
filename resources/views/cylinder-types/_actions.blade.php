@if((int) $model->status === \App\CylinderType::STATUS_ACTIVE)
    <a href="{{ route('cylinder-type.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this cylinder type as inactive?');">Inactive</a>
@else
    <a href="{{ route('cylinder-type.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.cylinder-type', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.cylinder-type',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
