@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Web Side Menu
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-bars"></i> Web Side Menu</a></li>
                <li class="active">List</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of Web Side Menu Items</h3>
                            <div class="box-tools">
                                <a href="{{ route('create.web-side-menu') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Add New Menu Item
                                </a>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="web-side-menu-table" class="table table-bordered table-striped datatable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Order</th>
                                            <th>Title</th>
                                            <th>Sub Title</th>
                                            <th>Slug</th>
                                            <th>Create URL</th>
                                            <th>Read URL</th>
                                            <th>Update URL</th>
                                            <th>Delete URL</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 20px;">
                                <form id="sortOrderForm" action="{{ route('update.web-side-menu.sort-order') }}" method="POST" style="display: none;">
                                    @csrf
                                    <div id="sortOrderInputs"></div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa fa-save"></i> Save Sort Order
                                    </button>
                                </form>
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
    <style>
        /* Gray background for slug column (not changeable) */
        #web-side-menu-table td.slug-column {
            background-color: #f5f5f5 !important;
        }
        #web-side-menu-table th.slug-column {
            background-color: #e0e0e0 !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#web-side-menu-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "web-side-menu/data",
                    type: "GET"
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'sort_order', name: 'sort_order', orderable: true, searchable: false},
                    {data: 'title', name: 'title'},
                    {data: 'sub_title', name: 'sub_title'},
                    {data: 'slug', name: 'slug', className: 'slug-column'},
                    {data: 'create_url', name: 'create_url'},
                    {data: 'read_url', name: 'read_url'},
                    {data: 'update_url', name: 'update_url'},
                    {data: 'delete_url', name: 'delete_url'},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[1, 'asc']],
                pageLength: 25,
                dom: 'lfrtip'
            });

            // Show sort order form when any sort order is changed
            $(document).on('change', '.sort-order-input', function() {
                updateSortOrderForm();
            });

            function updateSortOrderForm() {
                var form = $('#sortOrderForm');
                var inputs = $('#sortOrderInputs');
                inputs.empty();
                
                $('.sort-order-input').each(function() {
                    var id = $(this).data('id');
                    var order = $(this).val();
                    inputs.append('<input type="hidden" name="id[]" value="' + id + '">');
                    inputs.append('<input type="hidden" name="sort_order[]" value="' + order + '">');
                });
                
                form.show();
            }
        });
    </script>
@endsection

