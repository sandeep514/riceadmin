<div class="form-group col-md-6">
    <label>Paddy State</label>
    <select class="form-control" name="state_id">
        @foreach($paddyState as $k => $v)
            <option value="{{ $v->id }}" {{ ($data->state_id == $v->id)?'selected' : '' }}>{{ $v->state }}</option>
        @endforeach
    </select>
</div>
<input type="hidden" name="id" value="{{ $data->id }}">
<div class="form-group col-md-6">
    <label>Mandi</label>
    <input type="text" class="form-control" name="mandi" value="{{ $data->mandi }}">
</div>