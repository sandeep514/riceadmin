<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('designation') has-error @enderror">
            {!! Form::label('designation','Designation*') !!}
            {!! Form::text('designation',null,['class'=>'form-control','id'=>'designation']) !!}
            @error('designation')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
