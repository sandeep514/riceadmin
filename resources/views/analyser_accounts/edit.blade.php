@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Analyser
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('analyser-accounts.index') }}">Analyser</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Analyser Details</h3>
                        </div>
                        {!! Form::model($model, ['route'=>['analyser-accounts.update', $model->id], 'method'=>'PUT']) !!}
                        @include('analyser_accounts._form')
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('analyser-accounts.index') }}" class="btn btn-danger">Cancel</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
