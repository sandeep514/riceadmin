@if((int) $model->status === \App\MachineryEquipment::STATUS_ACTIVE)
    <a href="{{ route('machinery-equipment.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this machinery equipment as inactive?');">Inactive</a>
@else
    <a href="{{ route('machinery-equipment.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.machinery-equipment', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.machinery-equipment',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
