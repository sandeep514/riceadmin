<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('version_name') has-error @enderror">
            {!! Form::label('version_name','Version Number*') !!}
            {!! Form::text('version',null,['class'=>'form-control','id'=>'category']) !!}
            @error('version_name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
