<div class="box-body">
    <div class="row">
        <div class="form-group col-md-5 @error('particular') has-error @enderror">
            {!! Form::label('particular', 'Particular*') !!}
            {!! Form::text('particular', null, ['class' => 'form-control', 'id' => 'particular', 'maxlength' => 255, 'placeholder' => 'Enter particular']) !!}
            @error('particular')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-3 @error('input_type') has-error @enderror">
            {!! Form::label('input_type', 'Input type*') !!}
            {!! Form::select(
                'input_type',
                \App\VendorContainerParticular::inputTypeOptions(),
                isset($model) ? $model->input_type : old('input_type', \App\VendorContainerParticular::INPUT_TYPE_NUMBER),
                ['class' => 'form-control', 'id' => 'input_type']
            ) !!}
            @error('input_type')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('status') has-error @enderror">
            {!! Form::label('status', 'Status*') !!}
            {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], isset($model) ? $model->status : 1, ['class' => 'form-control', 'id' => 'status']) !!}
            @error('status')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12 @error('description') has-error @enderror">
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'id' => 'description', 'rows' => 4, 'placeholder' => 'Optional description']) !!}
            @error('description')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
