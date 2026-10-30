<script>
(function () {
    var cfg = window.__analyserAccountForm || {};
    var cities = cfg.cities || [];

    function fillCities(stateId, selectedId) {
        var city = document.getElementById('business_city');
        if (!city) return;
        city.innerHTML = '';
        var blank = document.createElement('option');
        blank.value = '';
        blank.textContent = 'Select city';
        city.appendChild(blank);
        if (!stateId) return;
        cities.forEach(function (row) {
            if (String(row.state_id) !== String(stateId)) return;
            var opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = row.name;
            if (String(row.id) === String(selectedId || '')) opt.selected = true;
            city.appendChild(opt);
        });
    }

    var stateEl = document.getElementById('business_state');
    if (stateEl) {
        stateEl.addEventListener('change', function () {
            fillCities(this.value, '');
        });
        fillCities(stateEl.value, cfg.selectedCity || '');
    }
})();
</script>
