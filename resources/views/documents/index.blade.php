@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Documents
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Documents Management</a></li>
                <li class="active">Documents</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of documents</h3>
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
    <script type="text/javascript" src="{{ asset('js/deals.js') }}"></script>
@endsection
