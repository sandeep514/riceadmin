@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Edit Rice Brand Form <small>Edit</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('master.list.rice.brand.quality') }}">Rice Brand Forms</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Edit Rice Brand Form</h3>
                        </div>
                        @if(Session::has('message'))
                            <div class="alert alert-info alert-dismissible" style="margin:10px;">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                {{ Session::get('message') }}
                            </div>
                        @endif
                        {!! Form::open(['route' => ['master.update.rice.brand.quality', $riceForm->id], 'method' => 'POST']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('riceType', 'Rice Type *') !!}
                                        <select class="form-control" name="riceType" required>
                                            <option value="basmati" {{ $riceForm->type == 'basmati' ? 'selected' : '' }}>Basmati</option>
                                            <option value="non-basmati" {{ $riceForm->type == 'non-basmati' ? 'selected' : '' }}>Non Basmati</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('name', 'Form Name *') !!}
                                        {!! Form::text('name', $riceForm->form_name, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Enter form name']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('order', 'Order (for sorting)') !!}
                                        {!! Form::number('order', $riceForm->order, ['class' => 'form-control', 'placeholder' => 'Enter sort order']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('status', 'Status *') !!}
                                        <select class="form-control" name="status" required>
                                            <option value="1" {{ $riceForm->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $riceForm->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('master.list.rice.brand.quality') }}" class="btn btn-default">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
