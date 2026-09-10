@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Indian Ports
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li>Service Providers</li>
                <li>Masters</li>
                <li><a href="{{ route('vendor-indian-ports') }}">Indian Ports</a></li>
                <li class="active">Create</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Indian port details</h3>
                        </div>
                        {!! Form::open(['route' => 'save.vendor-indian-port']) !!}
                            @include('vendor-indian-ports._form')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('vendor-indian-ports') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
