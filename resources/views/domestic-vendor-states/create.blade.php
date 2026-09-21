@extends('layouts.main')
@section('content')
<div class="content-wrapper">
<section class="content-header">
    <h1>States <small>Create</small></h1>
    <ol class="breadcrumb">
        <li>Domestic Vendor</li><li>Masters</li>
        <li><a href="{{ route('domestic-vendor-states') }}">States</a></li>
        <li class="active">Create</li>
    </ol>
</section>
<section class="content"><div class="box box-primary">
{!! Form::open(['route' => 'save.domestic-vendor-state']) !!}
@include('domestic-vendor-states._form')
<div class="box-footer"><button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-danger" href="{{ route('domestic-vendor-states') }}">Cancel</a></div>
{!! Form::close() !!}
</div></section></div>
@endsection
