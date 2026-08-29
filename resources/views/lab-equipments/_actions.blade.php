@if((int) $model->status === \App\LabEquipment::STATUS_ACTIVE)
    <a href="{{ route('lab-equipment.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this lab equipment as inactive?');">Inactive</a>
@else
    <a href="{{ route('lab-equipment.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.lab-equipment', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.lab-equipment',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
