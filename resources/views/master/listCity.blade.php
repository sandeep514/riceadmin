@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>List Cities <small>List</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Cities</li>
            </ol>
        </section>
        <section class="content">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Cities</h3>
                    <div class="pull-right">
                        <a href="{{ route('master.export.city') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <form method="post" action="{{ route('master.update.city.order') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped master-datatable" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order</th>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cities as $key => $value)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="state[]" value="{{ $value['state'] }}">
                                                <input type="text" name="order[]" value="{{ $value['state_order'] }}" class="form-control input-sm" style="width:60px">
                                            </td>
                                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $value['state']) }}</td>
                                            <td>
                                                <a class="btn btn-info btn-xs" href="{{ route('master.get.city', base64_encode($value['state'])) }}">Edit</a>
                                                <a class="btn btn-danger btn-xs" href="{{ route('master.delete.city', base64_encode($value['state'])) }}" onclick="return confirm('Delete this record?')">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Update Order</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(function(){ $('.master-datatable').DataTable({ pageLength: 25, order: [[0,'asc']], columnDefs: [{orderable: false, targets: [1,3]}] }); });
</script>
@endsection