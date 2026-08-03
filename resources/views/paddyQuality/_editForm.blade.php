<div class="box-body">
    <div class="row">
        <input type="hidden" name="id" value="{{ $data->id }}">
        <div class="form-group col-md-6">
            <label>Quality <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="quality" value="{{ old('quality', $data->quality) }}" required>
            @error('quality')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="3">{{ old('description', $data->description) }}</textarea>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
