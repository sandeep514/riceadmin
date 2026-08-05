@php
    $riceTypeOptions = $riceTypes ?? \App\RiceType::orderBy('name')->pluck('name', 'id');
    $selectedRiceType = old('rice_type_id', $data->rice_type_id);
@endphp
<div class="box-body">
    <div class="row">
        <input type="hidden" name="id" value="{{ $data->id }}">
        <div class="form-group col-md-6">
            <label>Rice Type <span class="text-danger">*</span></label>
            <select name="rice_type_id" class="form-control" required>
                <option value="">Select Rice Type</option>
                @foreach($riceTypeOptions as $id => $name)
                    <option value="{{ $id }}" {{ (string) $selectedRiceType === (string) $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            @error('rice_type_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6">
            <label>Quality <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="quality" value="{{ old('quality', $data->quality) }}" required>
            @error('quality')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-12">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="3">{{ old('description', $data->description) }}</textarea>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
