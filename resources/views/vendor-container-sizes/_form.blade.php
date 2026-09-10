<div class="box-body">
    <div class="row">
        <div class="form-group col-md-3 @error('size') has-error @enderror">
            {!! Form::label('size', 'Size (FT)*') !!}
            {!! Form::number('size', isset($model) ? $model->size : old('size'), ['class' => 'form-control', 'id' => 'size', 'min' => 1, 'placeholder' => '40']) !!}
            @error('size')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-5 @error('label') has-error @enderror">
            {!! Form::label('label', 'Label') !!}
            {!! Form::text('label', isset($model) ? $model->label : old('label'), ['class' => 'form-control', 'id' => 'label', 'maxlength' => 255, 'placeholder' => '40 FT']) !!}
            @error('label')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('status') has-error @enderror">
            {!! Form::label('status', 'Status*') !!}
            {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], isset($model) ? $model->status : old('status', 1), ['class' => 'form-control', 'id' => 'status']) !!}
            @error('status')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12 @error('description') has-error @enderror">
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', isset($model) ? $model->description : old('description'), ['class' => 'form-control', 'id' => 'description', 'rows' => 3]) !!}
            @error('description')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
