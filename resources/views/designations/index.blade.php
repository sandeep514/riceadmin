@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Designations
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> User Management</a></li>
                <li class="active">Designations</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of designations</h3>
                        </div>
                        <!-- /.box-header -->
                        <table id="example2" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center">Designation</th>
                                    <th style="text-align: center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($designation as $k => $v)
                                    <tr>
                                        <td style="text-align: center">{{ $v->designation }}</td>
                                        <td style="text-align: center">
                                            <a href="{{ route('edit.designation' , $v->id) }}" class="btn btn-info btn-xs">Edit</a>
                                            {{-- <a href="" class="btn btn-danger btn-xs">Delete</a> --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            
                            <tfoot>
                                <tr>
                                    <th style="text-align: center">Designation</th>
                                    <th style="text-align: center">Action</th>
                                </tr>
                            </tfoot>
                        </table>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
