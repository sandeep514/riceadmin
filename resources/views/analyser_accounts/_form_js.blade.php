<script>
(function () {
    var cfg = window.__analyserAccountForm || {};
    var cities = cfg.cities || [];
    var plansByCategory = cfg.plansByCategory || {};

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

    function fillPlans(categoryId, selectedId) {
        var plan = document.getElementById('analyser_plan_id');
        if (!plan) return;
        plan.innerHTML = '';
        var blank = document.createElement('option');
        blank.value = '';
        blank.textContent = 'Select plan';
        plan.appendChild(blank);
        if (!categoryId) return;
        var plans = plansByCategory[String(categoryId)] || plansByCategory[categoryId] || [];
        plans.forEach(function (row) {
            var opt = document.createElement('option');
            opt.value = String(row.id);
            opt.textContent = row.title;
            if (String(row.id) === String(selectedId || '')) opt.selected = true;
            plan.appendChild(opt);
        });
    }

    var stateEl = document.getElementById('business_state');
    var categoryEl = document.getElementById('analyser_category_id');

    if (stateEl) {
        stateEl.addEventListener('change', function () {
            fillCities(this.value, '');
        });
        fillCities(stateEl.value, cfg.selectedCity || '');
    }

    if (categoryEl) {
        categoryEl.addEventListener('change', function () {
            fillPlans(this.value, '');
        });
        fillPlans(categoryEl.value || cfg.selectedCategoryId || '', cfg.selectedPlanId || '');
    }
})();
</script>
