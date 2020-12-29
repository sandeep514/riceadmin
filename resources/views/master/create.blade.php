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
                            <h3 class="box-title">Rice City</h3>
                        </div>
                        {!! Form::open(['route'=>'master.create.city']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('name','City Name*') !!}
                                        {!! Form::text('name' , '' , ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Save Name</button>
                                    </div>
                                    <div class="col-md-8 " style="height: 35px">
                                        
                                    </div>
                                    <div class="col-md-2">
                                        <a class="btn btn-info" href="{{ route('master.list.city') }}">List Name</a>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Rice Name</h3>
                        </div>
                        {!! Form::open(['route'=>'master.create.rice.quality']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('riceType','Rice type*') !!}
                                        <select class="form-control" name="riceType">
                                            <option value="basmati">Basmati</option>
                                            <option value="non-basmati">Non Basmati</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('name','Rice Name*') !!} <small>(eg: 1121 , Sugandha )</small>
                                        {!! Form::text('name' , '' , ['class' => 'form-control']) !!}
                                    </div>
                                </div>

                            </div>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Save name</button>
                                    </div>
                                    <div class="col-md-8 " style="height: 35px">
                                        
                                    </div>
                                    <div class="col-md-2">
                                        <a class="btn btn-info" href="{{ route('master.list.rice.quality') }}">List Rice Name</a>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Rice Type</h3>
                        </div>
                        {!! Form::open(['route'=>'master.create.rice.type']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('riceType','Rice Type*') !!}
                                        <select class="form-control" name="riceType">
                                            <option value="basmati">Basmati</option>
                                            <option value="non-basmati">Non Basmati</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4 ">
                                        {!! Form::label('name','Rice name*') !!}<small>(eg: Creamy sella , Brown)</small>
                                        {!! Form::text('name' , '' , ['class' => 'form-control']) !!}
                                    </div>
                                </div>

                            </div>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Save Name</button>
                                    </div>
                                    <div class="col-md-8 " style="height: 35px">
                                        
                                    </div>
                                    <div class="col-md-2">
                                        <a class="btn btn-info" href="{{ route('master.list.rice.type') }}">List Rice Name</a>
                                    </div>
                                </div>

                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                
                

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Transport States</h3>
                        </div>
                        {!! Form::open(['route'=>'master.transport.create.state']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('name','Transport State*') !!}
                                        {!! Form::text('state' , '' , ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Save Transport States</button>
                                    </div>
                                    <div class="col-md-8 " style="height: 35px">
                                        
                                    </div>
                                    <div class="col-md-2">
                                        <a class="btn btn-info" href="{{ route('master.transport.list.state') }}">List Transport States</a>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Transport Route</h3>
                        </div>
                        {!! Form::open(['route'=>'master.transport.create.route']) !!}

                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('name','Transport Port*') !!}
                                        {!! Form::text('route' , '' , ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>                 
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Save Transport Route</button>
                                    </div>
                                    <div class="col-md-8 " style="height: 35px"></div>
                                    <div class="col-md-2">
                                        <a class="btn btn-info" href="{{ route('master.transport.list.route') }}">List Transport Route</a>
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