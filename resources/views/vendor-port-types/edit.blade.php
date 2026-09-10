@extends('layouts.main')
@section('content')
<div class="content-wrapper">
<section class="content-header"><h1>Port Types <small>Edit</small></h1>
<ol class="breadcrumb"><li>Service Providers</li><li>Masters</li><li><a href="{{ route('vendor-port-types') }}">Port Types</a></li><li class="active">Edit</li></ol>
</section>
<section class="content"><div class="box box-primary">
{!! Form::model($model, ['route' => ['update.vendor-port-type', $model->id], 'method' => 'PUT']) !!}
@include('vendor-port-types._form', ['model' => $model])
<div class="box-footer"><button class="btn btn-primary" type="submit">Update</button> <a class="btn btn-danger" href="{{ route('vendor-port-types') }}">Cancel</a></div>
{!! Form::close() !!}
</div></section></div>
@endsection
