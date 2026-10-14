<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('category') has-error @enderror">
            {!! Form::label('category', 'Category*') !!}
            {!! Form::text('category', null, ['class' => 'form-control', 'id' => 'category', 'maxlength' => 255, 'placeholder' => 'Enter category name']) !!}
            @error('category')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-3 @error('order') has-error @enderror">
            {!! Form::label('order', 'Display Order') !!}
            {!! Form::number('order', null, ['class' => 'form-control', 'id' => 'order', 'min' => 0, 'placeholder' => 'e.g. 10']) !!}
            @error('order')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-3 @error('status') has-error @enderror">
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
