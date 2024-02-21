@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{-- @php
                    $roleName = \App\Role::find(request()->role)->role_name;
                @endphp
                {{ $roleName }} Users --}}
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
                            <h3 class="box-title">List of users</h3>
                        </div>
                        <div>
                            @php
                                $from = request()->from;
                                $to = request()->to;
                            @endphp
                            <form method="GET" action="{{ route('get.total.users.with.date.filter') }}"> 
                                @csrf
                                <div class="container">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="from">From:</label>
                                            <input type="date" data-date-format="DD-MM-YYYY" class="form-control" id="from" name="from" value="{{ $from }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="to">To:</label>
                                            <input type="date" data-date-format="DD-MM-YYYY" class="form-control" id="to" name="to" value="{{ $to }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-info btn-sm" title="button" />
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                {!! $dataTable->table(['class'=>'table table-bordered table-striped datatable','width'=>'100%']) !!}
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    {!! $dataTable->scripts() !!}
@endsection
