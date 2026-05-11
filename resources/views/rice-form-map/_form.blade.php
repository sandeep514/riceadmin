<div class="box-body">

    {{-- Step 1: Rice Type --}}
    <div class="row">
        <div class="form-group col-md-6 @error('rice_type') has-error @enderror">
            <label for="rice_type">Rice Type <span class="text-danger">*</span></label>
            <select name="rice_type" id="rice_type" class="form-control">
                <option value="">-- Select Basmati / Non-Basmati --</option>
                <option value="basmati"     {{ (isset($model) && $model->rice_type == 'basmati')     ? 'selected' : (old('rice_type') == 'basmati'     ? 'selected' : '') }}>Basmati</option>
                <option value="non-basmati" {{ (isset($model) && $model->rice_type == 'non-basmati') ? 'selected' : (old('rice_type') == 'non-basmati' ? 'selected' : '') }}>Non-Basmati</option>
            </select>
            @error('rice_type')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Step 2: Rice Name (loaded via AJAX on rice_type change) --}}
    <div class="row">
        <div class="form-group col-md-6 @error('rice_name_id') has-error @enderror">
            <label for="rice_name_id">Rice Name <span class="text-danger">*</span></label>
            <select name="rice_name_id" id="rice_name_id" class="form-control select2-basic">
                <option value="">-- Select Rice Type First --</option>
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

    {{-- Step 3: Rice Form (from rice_form_milestone3, single select) --}}
    <div class="row">
        <div class="form-group col-md-8 @error('form_ids') has-error @enderror">
            <label for="form_ids">Rice Form <span class="text-danger">*</span></label>
            <select name="form_ids" id="form_ids" class="form-control select2-basic">
                <option value="">-- Select Rice Form --</option>
                @if(isset($riceForms))
                    @foreach($riceForms as $id => $formName)
                        <option value="{{ $id }}" {{ (isset($model) && $model->form_ids == $id) ? 'selected' : (old('form_ids') == $id ? 'selected' : '') }}>{{ $formName }}</option>
                    @endforeach
                @endif
            </select>
            @error('form_ids')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Step 4: Wand Types (loaded via AJAX on rice name change) --}}
    <div class="row">
        <div class="form-group col-md-8 @error('wand_ids') has-error @enderror">
            <label for="wand_ids" style="display:block;">
                Wand Types
                <span class="pull-right">
                    <button type="button" id="wand_check_all" class="btn btn-xs btn-default">Check All</button>
                    <button type="button" id="wand_uncheck_all" class="btn btn-xs btn-default">Uncheck All</button>
                </span>
            </label>
            <select name="wand_ids[]" id="wand_ids" class="form-control select2" multiple="multiple">
                <option value="">-- Select Rice Name First --</option>
            </select>
            @error('wand_ids')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

</div>
