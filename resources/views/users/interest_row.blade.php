@php
    $row = $row ?? ['rice_type' => '', 'name_id' => '', 'form_id' => '', 'grades' => []];
@endphp
<div
    class="interest-row panel panel-default"
    data-row-index="{{ $idx }}"
    data-initial-type="{{ $row['rice_type'] ?? '' }}"
    data-initial-name-id="{{ $row['name_id'] ?? '' }}"
    data-initial-form-id="{{ $row['form_id'] ?? '' }}"
    data-initial-grades="{{ e(json_encode($row['grades'] ?? [])) }}"
    style="margin-bottom:12px;"
>
    <div class="panel-heading clearfix">
        <span class="panel-title" style="font-size:14px;">Rice interest</span>
        <button type="button" class="btn btn-xs btn-danger pull-right js-remove-interest-row">Remove</button>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <label>Rice type</label>
                <select class="form-control js-interest-rice-type">
                    <option value="">— Select —</option>
                    <option value="basmati" {{ ($row['rice_type'] ?? '') === 'basmati' ? 'selected' : '' }}>Basmati</option>
                    <option value="non-basmati" {{ ($row['rice_type'] ?? '') === 'non-basmati' ? 'selected' : '' }}>Non-basmati</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Rice name</label>
                <select name="interested[{{ $idx }}][name_id]" class="form-control js-interest-rice-name">
                    <option value="">— Select —</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Form (milestone3)</label>
                <select name="interested[{{ $idx }}][form_id]" class="form-control js-interest-form">
                    <option value="">— Select —</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Wands (optional)</label>
                <select name="interested[{{ $idx }}][grades][]" class="form-control js-interest-wands" multiple size="5"></select>
                <p class="help-block small" style="margin-top:6px;">Empty = one row without a specific wand. Hold Ctrl/Cmd to pick several.</p>
            </div>
        </div>
    </div>
</div>
