@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Transport Route
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Transport</a></li>
                <li class="active">Edit Transport Route</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">

                        {!! Form::model($port,['route'=>['master.transport.update.route',$port->id],'method'=>'put']) !!}
                            {!! Form::hidden('id' , null) !!}
                            <div class="form-group col-md-4 ">
                                {!! Form::label('name','Transport Route*') !!}
                                {!! Form::text('route' ,null , ['class' => 'form-control']) !!}
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        {!! Form::close() !!}
                        
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
