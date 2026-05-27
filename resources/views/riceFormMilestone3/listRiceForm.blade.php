@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Rice Form 3 <small>List</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('master.index') }}">Master</a></li>
                <li class="active">Rice Form 3</li>
            </ol>
        </section>
        <section class="content">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">All Rice Form 3</h3>
                    <div class="pull-right">
                        <a href="{{ route('master.export.rice.form.milestone3') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
                        <a href="{{ route('master.create.rice.form.milestone3') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Create New
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped milestone3-datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forms as $form)
                                    <tr>
                                        <td>{{ $form->id }}</td>
                                        <td>{{ $form->name }}</td>
                                        <td>{{ $form->order }}</td>
                                        <td>
                                            @if($form->status == 1)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('master.edit.rice.form.milestone3', $form->id) }}" class="btn btn-info btn-xs">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            @if((int) $form->status === 1)
                                                <a href="{{ route('master.change.rice.form.milestone3.status', [$form->id, 0]) }}" class="btn btn-warning btn-xs" onclick="return confirm('Deactivate this record?')">Deactivate</a>
                                            @else
                                                <a href="{{ route('master.change.rice.form.milestone3.status', [$form->id, 1]) }}" class="btn btn-success btn-xs" onclick="return confirm('Activate this record?')">Activate</a>
                                            @endif
                                            {!! Form::open(['method'=>'DELETE','route'=>['master.delete.rice.form.milestone3',$form->id],'style'=>'display: inline-block;']) !!}
                                                <button type="submit" class="btn btn-danger btn-xs delete-row" onclick="return confirm('Are you sure you want to delete this?')">Delete</button>
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(function(){
        $('.milestone3-datatable').DataTable({
            pageLength: 25,
            order: [[2, 'asc']],
        });
    });
</script>
@endsection
