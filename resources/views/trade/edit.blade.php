@extends('layouts.main')

@section('content')
   
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Ports
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
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Price Details</h3>
                        </div>
                        <!-- /.box-header -->
                         {!! Form::open(['route'=>'master.trade.update', 'files' => true]) !!}
                            @include('trade.edit_form')
                            @if(!request()->has('date'))
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">Update Trade</button>
                                </div>
                            @endif
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
