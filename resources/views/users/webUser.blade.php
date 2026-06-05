@extends('layouts.main')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Web Users
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
                            <h3 class="box-title">List of web users</h3>
                        </div>
                        <a href="{{ route('mark.as.viewed') }}" class="btn btn-success btn-sm">Mark as viewed</a>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="example2" class="display" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">email </th>
                                            <th style="text-align: center">Phone </th>
                                            <th style="text-align: center">Category </th>
                                            <th style="text-align: center">Status </th>
                                            <th style="text-align: center">Action </th>
                                            <!-- <th style="text-align: center">country</th> -->
                                            <!-- <th style="text-align: center">Bag Vendor</th> -->
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($vendorUsers as $k => $v)
                                            <tr>
                                                <td style="text-align: center">
                                                    @if( $v->is_viewed_by_admin == 0 )
                                                        <img src="{{ asset('new-icon.png') }}" style="width: 45px">
                                                    @endif
                                                    
                                                    {{ $v->name ?? '--' }}</td>
                                                <td style="text-align: center">{{ $v->email ?? '--' }}</td>
                                                <td style="text-align: center">{{ ($v->phone)? $v->phone : $v->mobile  }}</td>

                                                <td style="text-align: center">{{ ($v->getWebBusinessDetails != null)?  ( $v->getWebBusinessDetails->getCategoryDetails )? ($v->getWebBusinessDetails->getCategoryDetails->category) : '--' : '--' }}</td>
                                                <td style="text-align: center">
                                                    @if((int) ($v->is_active_by_admin ?? 0) === 1)
                                                        <span class="label label-success">Active</span>
                                                    @else
                                                        <span class="label label-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td style="text-align: center">
                                                    <a href="{{ route('view.user', $v->id) }}" class="btn btn-info btn-xs">View</a>
                                                    @if((int) ($v->is_active_by_admin ?? 0) === 0)
                                                        <a href="{{ route('list.web.change.status.user', $v->id) }}" class="btn btn-success btn-xs" onclick="return confirm('Activate this user?');">Activate</a>
                                                    @else
                                                        <a href="{{ route('list.web.change.status.user', $v->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('De-activate this user?');">De-Activate</a>
                                                    @endif
                                                </td>
                                                <!-- <td style="text-align: center">{{ $v->country  }}</td> -->
                                                <!-- {{-- <td style="text-align: center">{{ $v->bagVendor->name  }}</td> --}} -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    
                                    <tfoot>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">email </th>
                                            <th style="text-align: center">Phone </th>
                                            <th style="text-align: center">Category </th>
                                            <th style="text-align: center">Status </th>
                                            <th style="text-align: center">Action </th>
                                            <!-- <th style="text-align: center">country</th> -->
                                            <!-- <th style="text-align: center">Bag Vendor</th> -->
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