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
                        {!! Form::open(['route'=>'master.transport.update.state','method'=>'post' ,'files' => 'true']) !!}
                            <input type="hidden" name="id" value="{{ $portState->id }}">
                            
                            <div class="form-group col-md-4 ">
                                {!! Form::label('state','State*') !!}
                                {!! Form::text('state' , $portState->state , ['class' => 'form-control']) !!}
                            </div>
                            
                            <div class="form-group col-md-4">
                                {!! Form::label('file','Upload file*') !!}
                                <input type="file" name="uploadBanner" class="form-control">
                                <img src="{{ asset('uploads/banner/'.$portState->banner) }}" style="width:100px; height:100px" >
                                @if($errors->has('fileFormat'))
                                    <div style="color: red">
                                        <span>{{ $errors->first('fileFormat') }}</span>
                                    </div>
                                @endIf
                            </div>
                            
                            <div class="form-group col-md-4">
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>

                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection
