<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('specification') has-error @enderror">
            {!! Form::label('specification', 'Specification*') !!}
            {!! Form::text('specification', null, ['class' => 'form-control', 'id' => 'specification', 'maxlength' => 255, 'placeholder' => 'Enter specification']) !!}
            @error('specification')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('spec_for') has-error @enderror">
            {!! Form::label('spec_for', 'Spec For*') !!}
            {!! Form::select('spec_for', ['' => '-- Select --'] + \App\VendorSpecification::specForOptions(), null, ['class' => 'form-control', 'id' => 'spec_for']) !!}
            @error('spec_for')
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
    <div class="row">
        <div class="form-group col-md-4 @error('status') has-error @enderror">
            {!! Form::label('status', 'Status*') !!}
            {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], isset($model) ? $model->status : 1, ['class' => 'form-control', 'id' => 'status']) !!}
            @error('status')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
