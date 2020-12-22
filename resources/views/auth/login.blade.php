@extends('layouts.app')

@section('content')
    {!! Form::open(['route'=>'login']) !!}

        <div class="form-group has-feedback @error('email') has-error @enderror">
            {!! Form::text('email','admin@gmail.com',['class'=>'form-control','placeholder'=>'Enter email id']) !!}
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            @error('email')
                <span class="help-block text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group has-feedback @error('password') has-error @enderror"">
            {!! Form::password('password',['class'=>'form-control','placeholder'=>'Enter password']) !!}
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            @error('password')
                <span class="help-block text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="row">
            <div class="col-xs-8">
                <div class="checkbox icheck">
                    <label>
                        <input type="checkbox"> Remember Me
                    </label>
                </div>
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                {!! Form::submit('Sign In',['class'=>'btn btn-primary btn-block btn-flat']) !!}
            </div>
            <!-- /.col -->
        </div>
    {!! Form::close() !!}
@endsection
