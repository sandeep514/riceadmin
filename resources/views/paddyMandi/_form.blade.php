<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label>Paddy State</label>
            <select class="form-control" name="state_id">
                @foreach($paddyState as $k => $v)
                    <option value="{{ $v->id }}">{{ $v->state }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            <label>Mandi</label>
            <input type="text" class="form-control" name="mandi" value="">
        </div>
    </div>
</div>
