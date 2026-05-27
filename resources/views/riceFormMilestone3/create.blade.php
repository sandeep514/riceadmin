@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Create Rice Form 3</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('master.index') }}">Master</a></li>
                <li><a href="{{ route('master.rice.form.milestone3') }}">Rice Form 3</a></li>
                <li class="active">Create</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">New Rice Form</h3>
                        </div>
                        {!! Form::open(['route'=>'master.save.rice.form.milestone3']) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-6 @error('name') has-error @enderror">
                                        <label for="name">Rice Form Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Steam Wand" value="{{ old('name') }}">
                                        @error('name')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('master.rice.form.milestone3') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
