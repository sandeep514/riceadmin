@extends('layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Create New Price
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Live Prices</a></li>
                <li class="active">Create</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Rice form milestone3</h3>
                        </div>
                        {!! Form::open(['route'=>'master.save.rice.form.milestone3']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('name','Rice Form*') !!}
                                        {!! Form::text('name' , '' , ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Save Name</button>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection