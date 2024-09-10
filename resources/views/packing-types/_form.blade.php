<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('name') has-error @enderror">
            {!! Form::label('name','Name*') !!}
            {!! Form::text('name',null,['class'=>'form-control','id'=>'category']) !!}
            @error('name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
