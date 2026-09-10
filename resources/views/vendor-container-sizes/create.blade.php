@extends('layouts.main')
@section('content')
<div class="content-wrapper">
<section class="content-header"><h1>Container Sizes <small>Create</small></h1>
<ol class="breadcrumb"><li>Service Providers</li><li>Masters</li><li><a href="{{ route('vendor-container-sizes') }}">Container Sizes</a></li><li class="active">Create</li></ol></section>
<section class="content"><div class="box box-primary">
{!! Form::open(['route'=>'save.vendor-container-size']) !!}
@include('vendor-container-sizes._form')
<div class="box-footer"><button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-danger" href="{{ route('vendor-container-sizes') }}">Cancel</a></div>
{!! Form::close() !!}
</div></section></div>
@endsection
