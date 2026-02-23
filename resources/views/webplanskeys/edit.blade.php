@extends('layouts.main')

@section('content')
{{ dd("jimk") }}
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Web Plan leys
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Web Plan leys</a></li>
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
                            <h3 class="box-title">Web Plan Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'web.plans.keys.update']) !!}
                            @include('webplanskeys._editForm')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('list.web.plans.keys') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>

                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection
