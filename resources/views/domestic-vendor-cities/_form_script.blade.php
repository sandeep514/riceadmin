@php
    $selectedState = old('state_id', isset($model) ? $model->state_id : '');
    $includeInactiveId = isset($model) ? (string) $model->state_id : '';
    $stateRows = ($states ?? collect())->map(function ($row) {
        return [
            'id' => (int) $row->id,
            'country_id' => (int) $row->country_id,
            'name' => $row->name,
            'status' => (int) $row->status,
        ];
    })->values();
@endphp
<script>
(function () {
    var states = @json($stateRows);
    var selectedState = @json((string) $selectedState);
    var includeInactiveId = @json($includeInactiveId);

    function fillStates(countryId, selectedId) {
        var $state = $('#state_id');
        $state.empty().append($('<option>', { value: '', text: 'Select state' }));
        if (!countryId) {
            return;
        }
        states.forEach(function (row) {
            if (String(row.country_id) !== String(countryId)) {
                return;
            }
            if (row.status !== 1 && String(row.id) !== String(includeInactiveId)) {
                return;
            }
            $state.append($('<option>', {
                value: row.id,
                text: row.name,
                selected: String(row.id) === String(selectedId)
            }));
        });
    }

    $('#country_id').on('change', function () {
        fillStates($(this).val(), '');
    });

    fillStates($('#country_id').val(), selectedState);
})();
</script>
