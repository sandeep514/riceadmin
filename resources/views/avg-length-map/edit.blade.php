@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Edit Avg Length Map <small>Edit</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('avg-length-map') }}">Avg Length Map</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Map details</h3>
                        </div>
                        {!! Form::model($model, ['route'=>['update.avg-length-map',$model->id],'method'=>'put']) !!}
                            @include('avg-length-map._form')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('avg-length-map') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
@include('avg-length-map._scripts', ['selectedWandIds' => $model->wand_ids ?? []])
@endsection
