@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Edit Rice Form <small>Milestone 3</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('master.rice.form.milestone3') }}">Rice Forms Milestone 3</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Edit Rice Form</h3>
                        </div>
                        {!! Form::open(['route'=>['master.update.rice.form.milestone3',$form->id],'method'=>'put']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-6 @error('name') has-error @enderror">
                                        <label for="name">Rice Form Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $form->name) }}">
                                        @error('name')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3 @error('order') has-error @enderror">
                                        <label for="order">Order <span class="text-danger">*</span></label>
                                        <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $form->order) }}">
                                        @error('order')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3 @error('status') has-error @enderror">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="1" {{ $form->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $form->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('master.rice.form.milestone3') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
