@php($actionText = true)
@if(\App\Permission::hasPermission('edit'))
    <a href="{{ route('edit.document',$model->id) }}" class="btn btn-info btn-xs">Edit</a>
    @php($actionText = false)
@endif
@if(\App\Permission::hasPermission('delete'))
    |
    {!! Form::open(['method'=>'DELETE','route'=>['delete.document',$model->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
    <a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
    {!! Form::close() !!}
    @php($actionText = false)
@endif

@if($actionText == true)
    <i>No Action found</i>
@endif
