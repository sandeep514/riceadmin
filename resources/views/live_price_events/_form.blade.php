@php
    $selectedType = old('quality_type_id', isset($event) ? $event->quality_type_id : '');
    $selectedQuality = old('quality_id', isset($event) ? $event->quality_id : '');
    $selectedForm = old('quality_form_id', isset($event) ? $event->quality_form_id : '');
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Title of Event <span class="text-danger">*</span></label>
            <input
                type="text"
                name="title"
                class="form-control"
                value="{{ old('title', isset($event) ? $event->title : '') }}"
                maxlength="255"
                required
            >
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Date of Event <span class="text-danger">*</span></label>
            <input type="date" name="event_date" class="form-control" value="{{ old('event_date', isset($event) ? $event->event_date : '') }}" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Quality Type (Optional)</label>
            <select name="quality_type_id" id="quality_type_id" class="form-control">
                <option value="">Select quality type</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ (string)$selectedType === (string)$type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Quality (Optional)</label>
            <select name="quality_id" id="quality_id" class="form-control">
                <option value="">Select quality</option>
                @foreach($qualities as $quality)
                    <option
                        value="{{ $quality->id }}"
                        data-type="{{ $quality->type }}"
                        {{ (string)$selectedQuality === (string)$quality->id ? 'selected' : '' }}
                    >
                        {{ $quality->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Quality Form (Optional)</label>
            <select name="quality_form_id" id="quality_form_id" class="form-control">
                <option value="">Select quality form</option>
                @foreach($forms as $form)
                    <option
                        value="{{ $form->id }}"
                        data-type="{{ $form->type }}"
                        {{ (string)$selectedForm === (string)$form->id ? 'selected' : '' }}
                    >
                        {{ $form->form_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Note <span class="text-danger">*</span></label>
    <textarea name="note" id="event_note" class="form-control" rows="8" required>{{ old('note', isset($event) ? $event->note : '') }}</textarea>
</div>

<div class="box-footer">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('live.price.events') }}" class="btn btn-default">Cancel</a>
</div>

@section('javascript')
<script type="text/javascript">
    (function () {
        function setOptionsVisible(selectId, typeValue) {
            var $select = $('#' + selectId);
            var selected = $select.val();
            $select.find('option').each(function () {
                var optionType = $(this).data('type');
                if (!typeValue || !optionType || String(optionType) === String(typeValue) || $(this).val() === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            if (typeValue && selected) {
                var selectedType = $select.find('option:selected').data('type');
                if (selectedType && String(selectedType) !== String(typeValue)) {
                    $select.val('');
                }
            }
        }

        function applyTypeFilter() {
            var typeValue = $('#quality_type_id').val();
            setOptionsVisible('quality_id', typeValue);
            setOptionsVisible('quality_form_id', typeValue);
        }

        $(document).ready(function () {
            applyTypeFilter();
            $('#quality_type_id').on('change', applyTypeFilter);

            // Rich text editor for Note (bold, bullets, lists, etc.)
            if ($.fn.wysihtml5) {
                $('#event_note').wysihtml5({
                    toolbar: {
                        'font-styles': true,
                        'emphasis': true,
                        'lists': true,
                        'html': false,
                        'link': true,
                        'image': false,
                        'color': false,
                        'blockquote': true
                    }
                });
            }
        });
    })();
</script>
@endsection
