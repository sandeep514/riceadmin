@extends('layouts.main')
@section('content')
    <div class="content-wrapper">
        
        <section class="content-header">
            <h1>
                Edit Rice Form
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Rice Form</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Rice Type</h3>
                        </div>
                        {!! Form::open(['route'=>'master.update.rice.quality']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('riceType','Rice Type*') !!}
                                        <select class="form-control" name="riceType">
                                            <option {{ ($riceName->type == "basmati") ? 'selected' : '' }} value="basmati">Basmati</option>
                                            <option {{ ($riceName->type == "non-basmati") ? 'selected' : '' }} value="non-basmati">Non Basmati</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4 ">
                                        {!! Form::label('name','Rice name*') !!}
                                        {!! Form::text('name' , $riceName->name , ['class' => 'form-control']) !!}
                                    </div>
                                    <input type="hidden" name="id" value="{{ $riceName->id }}">
                                </div>

                            </div>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Update Name</button>
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