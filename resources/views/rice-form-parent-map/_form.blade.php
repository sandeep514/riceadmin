<div class="box-body">

    <div class="row">
        <div class="form-group col-md-6 @error('parent_form_id') has-error @enderror">
            <label for="parent_form_id">Parent Rice Form <span class="text-danger">*</span></label>
            <select name="parent_form_id" id="parent_form_id" class="form-control select2-basic">
                <option value="">-- Select parent form (e.g. Steam) --</option>
                @foreach($riceForms as $id => $name)
                    <option value="{{ $id }}" {{ (isset($model) && $model->parent_form_id == $id) ? 'selected' : (old('parent_form_id') == $id ? 'selected' : '') }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('parent_form_id')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
            <p class="help-block">Example: Steam is the parent; child forms are variants under it.</p>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-8 @error('child_form_ids') has-error @enderror">
            <label for="child_form_ids" style="display:block;">
                Child Rice Forms <span class="text-danger">*</span>
                <span class="pull-right">
                    <button type="button" id="child_check_all" class="btn btn-xs btn-default">Check All</button>
                    <button type="button" id="child_uncheck_all" class="btn btn-xs btn-default">Uncheck All</button>
                </span>
            </label>
            <select name="child_form_ids[]" id="child_form_ids" class="form-control select2" multiple="multiple">
                @foreach($riceForms as $id => $name)
                    <option value="{{ $id }}"
                        {{ (isset($model) && in_array($id, $model->child_form_ids ?? [])) ? 'selected' : (is_array(old('child_form_ids')) && in_array($id, old('child_form_ids')) ? 'selected' : '') }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            @error('child_form_ids')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
            @error('child_form_ids.*')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
            <p class="help-block">e.g. Steam Grade A, Steam Grade A+</p>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-4 @error('status') has-error @enderror">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="1" {{ (isset($model) && $model->status == 1) || old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (isset($model) && $model->status == 0) || old('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

</div>
