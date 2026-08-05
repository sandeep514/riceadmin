@php
    $riceTypeOptions = $riceTypes ?? \App\RiceType::orderBy('name')->pluck('name', 'id');
@endphp
<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label>Rice Type <span class="text-danger">*</span></label>
            <select name="rice_type_id" class="form-control" required>
                <option value="">Select Rice Type</option>
                @foreach($riceTypeOptions as $id => $name)
                    <option value="{{ $id }}" {{ (string) old('rice_type_id') === (string) $id ? 'selected' : '' }}>
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
            <input type="text" class="form-control" name="quality" value="{{ old('quality') }}" required>
            @error('quality')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-12">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <p class="help-block">Order is assigned automatically (next number after the highest existing order).</p>
</div>
