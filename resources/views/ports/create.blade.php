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
                        
                            <div>
                                <form method="POST" action="{{ route('upload.image.state') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select name="image_state" class="form-control">
                                                @foreach($prices as $k => $v)
                                                    <option value="{{ $k }}"> {{ $k }}</option>
                                                @endforeach
                                            </select>        
                                        </div>
                                        <div class="col-md-4">
                                            <input type="file" name="file">                                            
                                        </div>
                                        <div class="col-md-4">
                                            <input type="submit" name="submit" value="Submit">                                            
                                        </div>
                                    </div>
                                </form>
                            </div>

                        {!! Form::open(['route'=>'ports.save']) !!}
                            @include('ports._form')
                            @if(!request()->has('date'))
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">Save Ports</button>
                                </div>
                            @endif
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
