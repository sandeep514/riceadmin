@extends('layouts.main')

@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                SQL Import
                <small>Import SQL File</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-database"></i> SQL Import</a></li>
                <li class="active">Import</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Import SQL File to Database</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'sql-import.import', 'method'=>'POST', 'files'=>true]) !!}
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-md-12 @error('database_name') has-error @enderror">
                                    {!! Form::label('database_name','Database Name*') !!}
                                    {!! Form::text('database_name', $dbName, ['class'=>'form-control','id'=>'database_name','placeholder'=>'Enter database name', 'required'=>'required']) !!}
                                    @error('database_name')
                                        <span class="help-block text-danger" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                    <small class="help-block">Enter the database name where you want to import the SQL file.</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12 @error('sql_file') has-error @enderror">
                                    {!! Form::label('sql_file','SQL File*') !!}
                                    {!! Form::file('sql_file', ['class'=>'form-control','id'=>'sql_file','accept'=>'.sql,.txt', 'required'=>'required']) !!}
                                    @error('sql_file')
                                        <span class="help-block text-danger" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                    <small class="help-block">Select a SQL file to import (No size limit - supports large files up to 2GB+). Accepted formats: .sql, .txt</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> <strong>Important:</strong>
                                        <ul style="margin-bottom: 0; padding-left: 20px;">
                                            <li>Make sure you have a backup of your database before importing.</li>
                                            <li>The SQL file will be executed statement by statement using streaming (memory efficient).</li>
                                            <li>For very large files (2GB+), the system will attempt to use MySQL command line tool for faster import.</li>
                                            <li>Any errors during execution will be logged and displayed.</li>
                                            <li>Large SQL files may take some time to process - please be patient.</li>
                                            <li>PHP execution time and memory limits are automatically increased for large files.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-upload"></i> Import SQL File
                            </button>
                            <a href="{{ route('sql-import') }}" class="btn btn-danger">Cancel</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        // Show file name when selected
        $('#sql_file').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            var fileSize = this.files[0] ? (this.files[0].size / (1024 * 1024)).toFixed(2) + ' MB' : '';
            if (fileName) {
                $(this).next('.help-block').html('Selected file: <strong>' + fileName + '</strong>' + (fileSize ? ' (' + fileSize + ')' : ''));
            }
        });
    });
</script>
@endsection

