@extends('layouts.main')

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Public Packing Master
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Public Packing Master</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Import from Excel</h3>
                    </div>
                    {!! Form::open(['route' => 'public.packing.master.controller', 'files' => true]) !!}
                        <div class="box-body">
                            <div class="form-group">
                                {!! Form::label('file', 'Upload Excel') !!}
                                {!! Form::file('file', ['required' => 'required', 'class' => 'form-control', 'id' => 'file']) !!}
                                <p class="help-block">Columns: size, packing, order</p>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Import</button>
                        </div>
                    {!! Form::close() !!}
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Public Packing List</h3>
                        <div class="box-tools">
                            <a href="{{ route('public.packing.master.create') }}" class="btn btn-sm btn-info">
                                <i class="fa fa-plus"></i> Add New
                            </a>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="example" class="display table table-striped table-bordered" style="width:100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Size</th>
                                <th>Packing</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $v)
                                <tr>
                                    <td>{{ $v->id }}</td>
                                    <td>{{ $v->size }}</td>
                                    <td>{{ $v->packing }}</td>
                                    <td>{{ $v->order }}</td>
                                    <td>
                                        @if((int) $v->status === 1)
                                            <span class="label label-success">Active</span>
                                        @else
                                            <span class="label label-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <a class="btn btn-sm btn-info" href="{{ route('public.packing.master.edit', $v->id) }}">
                                            Edit
                                        </a>
                                        <a class="btn btn-sm btn-{{ (int) $v->status === 1 ? 'danger' : 'success' }}"
                                           href="{{ route('public.packing.master.change.status', $v->id) }}"
                                           onclick="return confirm('{{ (int) $v->status === 1 ? 'Deactivate this packing?' : 'Activate this packing?' }}');">
                                            {{ (int) $v->status === 1 ? 'De-active' : 'Active' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#example').DataTable({
            pageLength: 25,
            order: [[3, 'asc']]
        });
    });
</script>
@endsection
