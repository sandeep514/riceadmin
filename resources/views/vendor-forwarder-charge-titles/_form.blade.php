<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('name') has-error @enderror">
            {!! Form::label('name', 'Name*') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'maxlength' => 255, 'placeholder' => 'e.g. Ori THC']) !!}
            @error('name')
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
        <div class="form-group col-md-12">
            {!! Form::hidden('is_required', 0) !!}
            <label style="font-weight: normal;">
                {!! Form::checkbox('is_required', 1, isset($model) ? (int) $model->is_required === 1 : true, ['id' => 'is_required']) !!}
                Required on vendor form (default amount 0)
            </label>
            <p class="help-block">Required charges are prefilled for the vendor at 0. Vendor extra charges are not added to this master.</p>
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
