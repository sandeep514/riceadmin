@extends('layouts.main')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Vendoe Users
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Users</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of vendor users</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="example2" class="display" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">email </th>
                                            <th style="text-align: center">country</th>
                                            <th style="text-align: center">Bag Vendor</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($vendorUsers as $k => $v)
                                            <tr>
                                                <td style="text-align: center">{{ $v->name }}</td>
                                                <td style="text-align: center">{{ $v->email  }}</td>
                                                <td style="text-align: center">{{ $v->country  }}</td>
                                                <td style="text-align: center">{{ $v->bagVendor->name  }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    
                                    <tfoot>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">email </th>
                                            <th style="text-align: center">country</th>
                                            <th style="text-align: center">Bag Vendor</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('scripts')