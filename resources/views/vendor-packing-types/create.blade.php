@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Packing Type
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li>Vendor Flow</li>
                <li><a href="{{ route('vendor-packing-types') }}">Packing Type</a></li>
                <li class="active">Create</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Packing type details</h3>
                        </div>
                        {!! Form::open(['route' => 'save.vendor-packing-type']) !!}
                            @include('vendor-packing-types._form')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('vendor-packing-types') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
