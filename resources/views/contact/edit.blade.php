@extends('layouts.main')

@section('content')
   
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Contact Details
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('contact.details.master') }}">Contact Details</a></li>
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
                            <h3 class="box-title">Price Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'plan.update']) !!}
                            @include('plans.edit_form')
                            @if(!request()->has('date'))
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">Update Plan</button>
                                </div>
                            @endif
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
