@extends('layouts.main')
@section('content')
<div class="content-wrapper">
<section class="content-header">
    <h1>States <small>Edit</small></h1>
    <ol class="breadcrumb">
        <li>Domestic Vendor</li><li>Masters</li>
        <li><a href="{{ route('domestic-vendor-states') }}">States</a></li>
        <li class="active">Edit</li>
    </ol>
</section>
<section class="content"><div class="box box-primary">
{!! Form::model($model, ['route' => ['update.domestic-vendor-state', $model->id], 'method' => 'PUT']) !!}
@include('domestic-vendor-states._form', ['model' => $model])
<div class="box-footer"><button class="btn btn-primary" type="submit">Update</button> <a class="btn btn-danger" href="{{ route('domestic-vendor-states') }}">Cancel</a></div>
{!! Form::close() !!}
</div></section></div>
@endsection
