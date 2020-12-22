@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Import Quality
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Quality</a></li>
                <li class="active">Import</li>
            </ol>
        </section>
        <section class="content">
            @if(session()->has('imported'))
                <div class="alert alert-success" role="alert">
                    {{ session('imported') }}
                </div>
            @endif
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Quality Import</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'import.quality.save','files'=>true]) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-6 @error('name') has-error @enderror">
                                        {!! Form::label('file','Select File*') !!}
                                        {!! Form::file('file',null,['class'=>'form-control','id'=>'category']) !!}
                                        @error('name')
                                            <span class="help-block text-danger" role="alert">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6 @error('name') has-error @enderror">
                                        {!! Form::label('file','Download Sample File') !!}
                                        <a href="{{ asset('sample-qualities.csv') }}" class="btn btn-primary">Download Sample File</a>
                                    </div>
                                </div>
                            </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('qualities') }}" class="btn btn-danger">Cancel</a>
                        </div>
                        {!! Form::close() !!}
                    </div>

                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection
