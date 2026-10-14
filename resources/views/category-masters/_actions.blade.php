@if((int) $model->status === 1)
    <a href="{{ route('category-master.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this Category as inactive?');">Inactive</a>
@else
    <a href="{{ route('category-master.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.category-master', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.category-master',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
