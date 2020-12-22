<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('code') has-error @enderror">
            {!! Form::label('code','Code (Display Weight)*') !!}
            {!! Form::text('code',null,['class'=>'form-control','id'=>'category']) !!}
            @error('code')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('value') has-error @enderror">
            {!! Form::label('value','Weight*') !!}
            {!! Form::text('value',null,['class'=>'form-control','id'=>'category']) !!}
            @error('value')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
