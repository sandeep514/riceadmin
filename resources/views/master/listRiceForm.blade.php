@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>List Rice Forms <small>List</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Rice Forms</li>
            </ol>
        </section>
        <section class="content">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Rice Forms</h3>
                    <div class="pull-right">
                        <a href="{{ route('master.export.rice.form') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped master-datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Rice Form Name</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riceForm as $key => $form)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $form->form_name }}</td>
                                        <td>{{ $form->type }}</td>
                                        <td>
                                            <a href="{{ route('master.get.rice.type', $form->id) }}" class="btn btn-info btn-xs">Edit</a>
                                            <a href="{{ route('master.delete.rice.type', $form->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this record?')">Delete</a>
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
    $(function(){ $('.master-datatable').DataTable({ pageLength: 25, order: [[0,'asc']] }); });
</script>
@endsection