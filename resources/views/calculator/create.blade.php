@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Calculator
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('designations') }}">Calculator</a></li>
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
                            <h3 class="box-title">Calculator Details</h3>
                        </div>
                        <!-- /.box-header -->
                        @include('calculator._form')
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary calculate">Calculate</button>
                        </div>
                    </div>

                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>

@endsection
