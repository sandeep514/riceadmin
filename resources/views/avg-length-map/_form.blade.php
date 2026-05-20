<div class="box-body">

    <div class="row">
        <div class="form-group col-md-6 @error('quality_type') has-error @enderror">
            <label for="quality_type">Category <span class="text-danger">*</span></label>
            <select name="quality_type" id="quality_type" class="form-control">
                <option value="">-- Select Basmati / Non-Basmati --</option>
                <option value="basmati" {{ (isset($model) && $model->quality_type == 'basmati') ? 'selected' : (old('quality_type') == 'basmati' ? 'selected' : '') }}>Basmati</option>
                <option value="non-basmati" {{ (isset($model) && $model->quality_type == 'non-basmati') ? 'selected' : (old('quality_type') == 'non-basmati' ? 'selected' : '') }}>Non-Basmati</option>
            </select>
            @error('quality_type')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6 @error('rice_name_id') has-error @enderror">
            <label for="rice_name_id">Quality (Rice Name) <span class="text-danger">*</span></label>
            <select name="rice_name_id" id="rice_name_id" class="form-control select2-basic">
                <option value="">-- Select category first --</option>
                @if(isset($riceNames))
                    @foreach($riceNames as $id => $name)
                        <option value="{{ $id }}" {{ (isset($model) && $model->rice_name_id == $id) ? 'selected' : (old('rice_name_id') == $id ? 'selected' : '') }}>{{ $name }}</option>
                    @endforeach
                @endif
            </select>
            @error('rice_name_id')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6 @error('form_id') has-error @enderror">
            <label for="form_id">Type (Rice Form) <span class="text-danger">*</span></label>
            <select name="form_id" id="form_id" class="form-control select2-basic">
                <option value="">-- Select rice form --</option>
                @if(isset($riceForms))
                    @foreach($riceForms as $id => $formName)
                        <option value="{{ $id }}" {{ (isset($model) && $model->form_id == $id) ? 'selected' : (old('form_id') == $id ? 'selected' : '') }}>{{ $formName }}</option>
                    @endforeach
                @endif
            </select>
            @error('form_id')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-8 @error('wand_ids') has-error @enderror">
            <label for="wand_ids" style="display:block;">
                Grade
                <span class="pull-right">
                    <button type="button" id="wand_check_all" class="btn btn-xs btn-default">Check All</button>
                    <button type="button" id="wand_uncheck_all" class="btn btn-xs btn-default">Uncheck All</button>
                </span>
            </label>
            <select name="wand_ids[]" id="wand_ids" class="form-control select2" multiple="multiple">
                <option value="">-- Select quality first --</option>
            </select>
            @error('wand_ids')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
            @error('wand_ids.*')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

</div>
