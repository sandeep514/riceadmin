@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Quality
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Qualities</a></li>
                <li class="active">Edit Quality</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Quality Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::model($model,['route'=>['update.quality',$model->id],'method'=>'put']) !!}
                            @include('qualities._form')
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
