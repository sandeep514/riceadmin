@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>List Rice Brand Forms <small>List</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Rice Brand Forms</li>
            </ol>
        </section>
        <section class="content">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Rice Brand Forms</h3>
                    <div class="pull-right">
                        <a href="{{ route('master.export.rice.brand.form') }}" class="btn btn-success btn-sm">
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
                                    <th>Rice Brand Form Name</th>
                                    <th>Type</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riceForm as $key => $form)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $form->form_name }}</td>
                                        <td>{{ ucfirst($form->type) }}</td>
                                        <td>{{ $form->order ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $form->status == 1 ? 'success' : 'danger' }}">
                                                {{ $form->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('master.edit.rice.brand.quality', $form->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Edit</a>
                                            @if($form->status == 0)
                                                <a href="{{ route('master.delete.rice.brand.quality', $form->id) }}" class="btn btn-info btn-xs">Activate</a>
                                            @else
                                                <a href="{{ route('master.delete.rice.brand.quality', $form->id) }}" class="btn btn-danger btn-xs">De-Activate</a>
                                            @endif
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
    $(function(){ $('.master-datatable').DataTable({ pageLength: 25, order: [[3,'asc']] }); });
</script>
@endsection
