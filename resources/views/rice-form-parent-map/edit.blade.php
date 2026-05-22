@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Edit Rice Form Parent Map <small>Edit</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('rice-form-parent-map') }}">Rice Form Parent Map</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Map parent to child forms</h3>
                        </div>
                        {!! Form::model($model, ['route'=>['update.rice-form-parent-map', $model->id], 'method'=>'PUT']) !!}
                            @php $selectedChildIds = $model->child_form_ids ?? []; @endphp
                            @include('rice-form-parent-map._form')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('rice-form-parent-map') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
@include('rice-form-parent-map._scripts')
@endsection
