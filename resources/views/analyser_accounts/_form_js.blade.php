<script>
(function () {
    var cfg = window.__analyserAccountForm || {};
    var cities = cfg.cities || [];
    var plansByCategory = cfg.plansByCategory || {};
    var subscriptionDays = cfg.subscriptionDays || { monthly: 30, half_yearly: 183, yearly: 365 };
    var amountTouched = false;

    function findPlan(planId) {
        if (!planId) return null;
        var keys = Object.keys(plansByCategory);
        for (var i = 0; i < keys.length; i++) {
            var list = plansByCategory[keys[i]] || [];
            for (var j = 0; j < list.length; j++) {
                if (String(list[j].id) === String(planId)) {
                    return list[j];
                }
            }
        }
        return null;
    }

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

    function addDays(dateStr, days) {
        if (!dateStr) return '';
        var parts = String(dateStr).split('-');
        if (parts.length !== 3) return '';
        var d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
        if (isNaN(d.getTime())) return '';
        d.setDate(d.getDate() + Number(days || 0));
        var y = d.getFullYear();
        var m = ('0' + (d.getMonth() + 1)).slice(-2);
        var day = ('0' + d.getDate()).slice(-2);
        return y + '-' + m + '-' + day;
    }

    function fillAmountFromPlan() {
        if (amountTouched) return;
        var planEl = document.getElementById('analyser_plan_id');
        var typeEl = document.getElementById('analyser_subscription_type');
        var amountEl = document.getElementById('analyser_received_amount');
        if (!planEl || !typeEl || !amountEl) return;
        var plan = findPlan(planEl.value);
        var type = typeEl.value;
        if (!plan || !type || !plan.prices) return;
        var price = plan.prices[type];
        if (price === null || price === undefined || price === '') return;
        amountEl.value = price;
    }

    function fillEndDateFromType() {
        var startEl = document.getElementById('analyser_start_date');
        var endEl = document.getElementById('analyser_end_date');
        var typeEl = document.getElementById('analyser_subscription_type');
        if (!startEl || !endEl || !typeEl) return;
        var type = typeEl.value;
        if (!type || !startEl.value) return;
        var days = subscriptionDays[type];
        if (!days) return;
        endEl.value = addDays(startEl.value, days);
    }

    var stateEl = document.getElementById('business_state');
    var categoryEl = document.getElementById('analyser_category_id');
    var planEl = document.getElementById('analyser_plan_id');
    var typeEl = document.getElementById('analyser_subscription_type');
    var startEl = document.getElementById('analyser_start_date');
    var amountEl = document.getElementById('analyser_received_amount');

    if (stateEl) {
        stateEl.addEventListener('change', function () {
            fillCities(this.value, '');
        });
        fillCities(stateEl.value, cfg.selectedCity || '');
    }

    if (categoryEl) {
        categoryEl.addEventListener('change', function () {
            fillPlans(this.value, '');
            amountTouched = false;
            fillAmountFromPlan();
        });
        fillPlans(categoryEl.value || cfg.selectedCategoryId || '', cfg.selectedPlanId || '');
    }

    if (planEl) {
        planEl.addEventListener('change', function () {
            amountTouched = false;
            fillAmountFromPlan();
        });
    }

    if (typeEl) {
        typeEl.addEventListener('change', function () {
            amountTouched = false;
            fillAmountFromPlan();
            fillEndDateFromType();
        });
    }

    if (startEl) {
        startEl.addEventListener('change', function () {
            fillEndDateFromType();
        });
    }

    if (amountEl) {
        if (String(amountEl.value || '').trim() !== '') {
            amountTouched = true;
        }
        amountEl.addEventListener('input', function () {
            amountTouched = true;
        });
    }

    // Initial fill when creating / empty amount and plan + type already present.
    fillAmountFromPlan();
})();
</script>
