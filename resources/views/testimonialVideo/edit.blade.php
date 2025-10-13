@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Testimonial
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Testimonial</a></li>
                <li class="active">Edit Testimonial</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Testimonial Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::model($model,['route'=>['testimonial.video.update',$model->id],'method'=>'post','files' => true]) !!}
                        <input type="hidden" name="id" value={{$model->id}}>
                        @include('testimonial.video._form')
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                        {!! Form::close() !!}
                    </div>

                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection
