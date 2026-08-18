@if((int) $model->status === \App\VendorSpecFor::STATUS_ACTIVE)
    <a href="{{ route('vendor-spec-for.change-status', $model->id) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this Spec For as inactive?');">Inactive</a>
@else
    <a href="{{ route('vendor-spec-for.change-status', $model->id) }}" class="btn btn-success btn-xs">Active</a>
@endif
<a href="{{ route('edit.vendor-spec-for', $model->id) }}" class="btn btn-info btn-xs">Edit</a>
{!! Form::open(['method'=>'DELETE','route'=>['delete.vendor-spec-for',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
<a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
{!! Form::close() !!}
