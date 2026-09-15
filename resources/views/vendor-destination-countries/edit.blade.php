@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Destination Countries
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li>Service Providers</li>
                <li>Masters</li>
                <li><a href="{{ route('vendor-destination-countries') }}">Destination Countries</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Destination country details</h3>
                        </div>
                        {!! Form::model($model, ['route' => ['update.vendor-destination-country', $model->id], 'method' => 'PUT']) !!}
                            @include('vendor-destination-countries._form', ['model' => $model])
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('vendor-destination-countries') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
