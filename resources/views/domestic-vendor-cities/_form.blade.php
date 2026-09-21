@php
    $selectedCountry = old('country_id', isset($model) ? optional($model->state)->country_id : '');
    $selectedState = old('state_id', isset($model) ? $model->state_id : '');
@endphp
<div class="box-body">
    <div class="row">
        <div class="form-group col-md-4">
            {!! Form::label('country_id', 'Country*') !!}
            {!! Form::select('country_id', ['' => 'Select country'] + ($countries ?? []), $selectedCountry, ['class' => 'form-control', 'id' => 'country_id']) !!}
        </div>
        <div class="form-group col-md-4 @error('state_id') has-error @enderror">
            {!! Form::label('state_id', 'State*') !!}
            <select name="state_id" id="state_id" class="form-control">
                <option value="">Select state</option>
            </select>
            @error('state_id')<span class="help-block text-danger" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="form-group col-md-4 @error('status') has-error @enderror">
            {!! Form::label('status', 'Status*') !!}
            {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], isset($model) ? $model->status : 1, ['class' => 'form-control', 'id' => 'status']) !!}
            @error('status')<span class="help-block text-danger" role="alert">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6 @error('name') has-error @enderror">
            {!! Form::label('name', 'City*') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'maxlength' => 255, 'placeholder' => 'Enter city']) !!}
            @error('name')<span class="help-block text-danger" role="alert">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12 @error('description') has-error @enderror">
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'id' => 'description', 'rows' => 4, 'placeholder' => 'Optional description']) !!}
            @error('description')<span class="help-block text-danger" role="alert">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
