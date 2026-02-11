@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Web Side Menu
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('web-side-menu') }}">Web Side Menu</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Web Side Menu Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>['update.web-side-menu', $model->id], 'method'=>'PUT']) !!}
                        @include('web-side-menu._form')
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('web-side-menu') }}" class="btn btn-danger">Cancel</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection

