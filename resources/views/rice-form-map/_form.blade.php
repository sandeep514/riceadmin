<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('rice_name_id') has-error @enderror">
            {!! Form::label('rice_name_id','Rice Name*') !!}
            {!! Form::select('rice_name_id', $riceNames, null, ['class'=>'form-control','id'=>'rice_name_id','placeholder'=>'Select Rice Name']) !!}
            @error('rice_name_id')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-6 @error('group_name') has-error @enderror">
            {!! Form::label('group_name','Group Name*') !!}
            {!! Form::text('group_name', null, ['class'=>'form-control','id'=>'group_name','placeholder'=>'e.g. Steam']) !!}
            @error('group_name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6 @error('form_ids') has-error @enderror">
            {!! Form::label('form_ids','Forms*') !!}
            <select name="form_ids[]" class="form-control select2" id="form_ids" multiple="multiple">
                @foreach($riceForms as $id => $formName)
                    <option value="{{ $id }}" {{ (isset($model) && $model->form_ids && in_array($id, $model->form_ids)) ? 'selected' : '' }}>{{ $formName }}</option>
                @endforeach
            </select>
            @error('form_ids')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
