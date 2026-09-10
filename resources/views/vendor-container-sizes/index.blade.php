@extends('layouts.main')
@section('content')
<div class="content-wrapper">
<section class="content-header"><h1>Container Sizes <small>List</small></h1>
<ol class="breadcrumb"><li>Service Providers</li><li>Masters</li><li class="active">Container Sizes</li></ol></section>
<section class="content"><div class="box">
<div class="box-header"><h3 class="box-title">Container size master</h3>
<div class="pull-right"><a href="{{ route('create.vendor-container-size') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create New</a></div></div>
<div class="box-body table-responsive">
<table class="table table-bordered table-striped datatable" width="100%">
<thead><tr><th>#</th><th>Size</th><th>Label</th><th>Description</th><th>Status</th><th>Updated</th><th width="180">Action</th></tr></thead>
<tbody>
@foreach($records as $record)
<tr>
<td>{{ $record->id }}</td>
<td>{{ $record->size }}</td>
<td>{{ $record->label ?: ($record->size.' FT') }}</td>
<td>{{ $record->description ?: '—' }}</td>
<td>@if((int)$record->status===\App\VendorContainerSize::STATUS_ACTIVE)<span class="label label-success">Active</span>@else<span class="label label-default">Inactive</span>@endif</td>
<td>{{ $record->updated_at ? $record->updated_at->format('d M Y H:i') : '—' }}</td>
<td>@include('vendor-container-sizes._actions', ['model'=>$record])</td>
</tr>
@endforeach
</tbody></table></div></div></section></div>
@endsection
@section('javascript')
<script>
$(function(){
  $('.datatable').DataTable({ pageLength:25, order:[[1,'asc']], columnDefs:[{orderable:false, targets:[6]}] });
  $(document).on('click','.delete-row',function(e){ e.preventDefault(); if(confirm('Delete this container size?')) $(this).closest('form').submit(); });
});
</script>
@endsection
