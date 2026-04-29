@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Wands <small>List</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Wands</li>
            </ol>
        </section>

        <section class="content">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Rice Name Wand List</h3>
                    <div class="pull-right">
                        <a href="{{ route('master.wand.export') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped wand-datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Rice Name / Quality</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $key => $value)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</td>
                                        <td>
                                            <a class="btn btn-info btn-xs" href="{{ route('master.wand.create', base64_encode($value)) }}">
                                                <i class="fa fa-edit"></i> Update Wand
                                            </a>
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
    $(function(){ $('.wand-datatable').DataTable({ pageLength: 25, order: [[0,'asc']] }); });
</script>
@endsection
