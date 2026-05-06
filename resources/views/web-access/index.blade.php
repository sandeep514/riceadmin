@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Web Access
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-lock"></i> Web Access</a></li>
                <li class="active">List</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of Web Access Permissions</h3>
                            <div class="box-tools">
                                <a href="{{ route('create.web-access') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Add New Access
                                </a>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="web-access-table" class="table table-bordered table-striped datatable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Role</th>
                                            <th>Category</th>
                                            <th>Plan</th>
                                            <th>Menu Items</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#web-access-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: window.route+"/web-access/data",
                    type: "GET"
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'role_id', name: 'role_id'},
                    {data: 'category_id', name: 'category_id'},
                    {data: 'plan_id', name: 'plan_id'},
                    {data: 'menu_items_count', name: 'menu_items_count', orderable: false, searchable: false},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                dom: 'lfrtip',
                searching: true,
                paging: true
            });
        });
    </script>
@endsection

