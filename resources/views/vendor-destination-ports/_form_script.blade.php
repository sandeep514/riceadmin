@php
    $selectedCountry = old('country_id', isset($model) ? $model->country_id : '');
    $countryRows = ($countries ?? collect())->map(function ($row) {
        return [
            'id' => (int) $row->id,
            'region_id' => (int) $row->region_id,
            'name' => $row->name,
            'status' => (int) $row->status,
        ];
    })->values();
@endphp
<script>
    (function () {
        var countries = @json($countryRows);
        var selectedCountry = @json((string) $selectedCountry);
        var includeInactiveId = @json(isset($model) ? (string) $model->country_id : '');

        function fillCountries(regionId, selectedId) {
            var $country = $('#country_id');
            $country.empty().append($('<option>', { value: '', text: 'Select country' }));
            if (!regionId) {
                return;
            }
            countries.forEach(function (row) {
                if (String(row.region_id) !== String(regionId)) {
                    return;
                }
                if (row.status !== 1 && String(row.id) !== String(includeInactiveId)) {
                    return;
                }
                $country.append($('<option>', {
                    value: row.id,
                    text: row.name,
                    selected: String(row.id) === String(selectedId)
                }));
            });
        }

        $('#region_id').on('change', function () {
            fillCountries($(this).val(), '');
        });

        fillCountries($('#region_id').val(), selectedCountry);
    })();
</script>
