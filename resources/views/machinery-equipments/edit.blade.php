@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Machinery Equipment
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li>Vendor Flow</li>
                <li><a href="{{ route('machinery-equipments') }}">Machinery Equipment</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Machinery equipment details</h3>
                        </div>
                        {!! Form::model($model, ['route' => ['update.machinery-equipment', $model->id], 'method' => 'PUT']) !!}
                            @include('machinery-equipments._form', ['model' => $model])
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('machinery-equipments') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
