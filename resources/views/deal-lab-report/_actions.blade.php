<a href="{{ route('edit.deal-lab-report',$model->id) }}" class="btn btn-info btn-xs">Edit</a> |
{!! Form::open(['method'=>'DELETE','route'=>['delete.deal-lab-report',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
