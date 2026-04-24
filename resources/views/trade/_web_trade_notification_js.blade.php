(function () {
    var base = (typeof window.route !== 'undefined' && window.route) ? window.route.replace(/\/$/, '') : '{{ url('/administrator') }}';

    function getSelectedCategoryIds() {
        var ids = [];
        $('#trade-web-categories-grid input[name="category_ids[]"]:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function toggleNotifyFields() {
        var on = $('input[name="trade_notify_send"]:checked').val() === '1';
        if (on) {
            $('#trade-notify-fields').show();
        } else {
            $('#trade-notify-fields').hide();
        }
        if (on) {
            toggleAudience();
        }
    }

    function toggleAudience() {
        if ($('input[name="trade_notify_send"]:checked').val() !== '1') {
            return;
        }
        var mode = $('input[name="trade_notify_audience"]:checked').val();
        if (mode === 'selected_users') {
            $('#trade-notify-user-multiselect-wrap').show();
            loadNotifyUsers();
        } else {
            $('#trade-notify-user-multiselect-wrap').hide();
        }
    }

    function loadNotifyUsers() {
        var ids = getSelectedCategoryIds();
        var $sel = $('#trade_notify_user_ids');
        if ($.fn.select2 && $sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }
        $sel.empty();
        if (!ids.length) {
            if ($.fn.select2) {
                $sel.select2({ width: '100%' });
            }
            return;
        }
        $.getJSON(base + '/trade/web-notification-users', { category_ids: ids })
            .done(function (res) {
                if (!res || !res.data) {
                    return;
                }
                res.data.forEach(function (u) {
                    var label = (u.name || '') + ' — ' + (u.mobile || '') + ' (ID ' + u.id + ')';
                    $sel.append($('<option>', { value: u.id, text: label }));
                });
                if ($.fn.select2) {
                    $sel.select2({ width: '100%' });
                }
            });
    }

    $('input[name="trade_notify_send"]').on('change ifChanged ifToggled click', function () {
        setTimeout(toggleNotifyFields, 0);
    });
    $('input[name="trade_notify_audience"]').on('change ifChanged ifToggled click', function () {
        setTimeout(toggleAudience, 0);
    });

    $(document).on('ifChanged', '#trade-web-categories-grid input[name="category_ids[]"]', function () {
        if ($('input[name="trade_notify_send"]:checked').val() === '1' &&
            $('input[name="trade_notify_audience"]:checked').val() === 'selected_users') {
            loadNotifyUsers();
        }
    });

    $('#trade-web-categories-grid input[name="category_ids[]"]').on('change', function () {
        if ($('input[name="trade_notify_send"]:checked').val() === '1' &&
            $('input[name="trade_notify_audience"]:checked').val() === 'selected_users') {
            loadNotifyUsers();
        }
    });

    if ($.fn.select2 && $('#trade_notify_user_ids').length && !$('#trade_notify_user_ids').hasClass('select2-hidden-accessible')) {
        $('#trade_notify_user_ids').select2({ width: '100%' });
    }

    toggleNotifyFields();
    setTimeout(toggleNotifyFields, 150);
})();
