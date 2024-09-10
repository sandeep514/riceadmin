<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('role_name') has-error @enderror">
            {!! Form::label('role_name','Role Name*') !!}
            {!! Form::text('role_name',null,['class'=>'form-control','id'=>'category']) !!}
            @error('role_name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
