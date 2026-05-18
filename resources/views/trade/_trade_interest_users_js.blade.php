(function () {
    var base = (typeof window.route !== 'undefined' && window.route) ? window.route.replace(/\/$/, '') : '{{ url('/administrator') }}';
    var loadTimer = null;

    function interestNotifyOn() {
        return $('input[name="trade_interest_notify_send"]:checked').val() === '1';
    }

    function toggleInterestNotifyFields() {
        if (interestNotifyOn()) {
            $('#trade-interest-notify-fields').show();
        } else {
            $('#trade-interest-notify-fields').hide();
        }
    }

    function getTradeInterestParams() {
        var quality = $('select[name=quality]').val();
        var form = $('select[name=riceform]').val();
        var grade = $('select[name=ricegrade]').val();
        if (!quality || !form || !grade) {
            return null;
        }
        return { quality: quality, riceform: form, ricegrade: grade };
    }

    function renderInterestUsers(users) {
        var $list = $('#trade-interest-users-list');
        var $empty = $('#trade-interest-users-empty');
        var $count = $('#trade-interest-users-count');
        $list.empty();

        if (!users || !users.length) {
            $empty.show();
            $count.text('');
            $('#trade-interest-select-all').prop('checked', false).prop('disabled', true);
            return;
        }

        $empty.hide();
        $count.text('(' + users.length + ' user' + (users.length === 1 ? '' : 's') + ')');
        $('#trade-interest-select-all').prop('disabled', false).prop('checked', true);

        users.forEach(function (u) {
            var label = (u.name || 'User') + ' — ' + (u.mobile || '') + ' (ID ' + u.id + ')';
            var $row = $('<label class="checkbox" style="display:block;margin:6px 0;font-weight:normal;"></label>');
            $row.append(
                $('<input type="checkbox" name="trade_interest_notify_user_ids[]">')
                    .attr('value', u.id)
                    .addClass('trade-interest-user-cb')
                    .prop('checked', true)
            );
            $row.append(document.createTextNode(' ' + label));
            $list.append($row);
        });
    }

    function loadInterestUsers() {
        var params = getTradeInterestParams();
        if (!params) {
            $('#trade-interest-users-panel').hide();
            return;
        }

        $('#trade-interest-users-panel').show();
        $('#trade-interest-users-loading').show();
        $('#trade-interest-users-empty').hide();
        $('#trade-interest-users-list').empty();

        $.getJSON(base + '/trade/interested-users', params)
            .done(function (res) {
                renderInterestUsers(res && res.data ? res.data : []);
            })
            .fail(function () {
                renderInterestUsers([]);
            })
            .always(function () {
                $('#trade-interest-users-loading').hide();
            });
    }

    function scheduleLoad() {
        clearTimeout(loadTimer);
        loadTimer = setTimeout(loadInterestUsers, 250);
    }

    $('select[name=quality], select[name=riceform]').on('change', function () {
        $('#trade-interest-users-panel').hide();
        $('#trade-interest-users-list').empty();
        $('#trade-interest-users-empty').hide();
        $('#trade-interest-users-count').text('');
    });

    $('select[name=ricegrade]').on('change', scheduleLoad);

    $('input[name="trade_interest_notify_send"]').on('change ifChanged ifToggled click', function () {
        setTimeout(toggleInterestNotifyFields, 0);
    });

    $('#trade-interest-select-all').on('change', function () {
        var checked = $(this).is(':checked');
        $('.trade-interest-user-cb').prop('checked', checked);
    });

    $(document).on('change', '.trade-interest-user-cb', function () {
        var $boxes = $('.trade-interest-user-cb');
        if (!$boxes.length) {
            return;
        }
        var all = $boxes.length === $boxes.filter(':checked').length;
        $('#trade-interest-select-all').prop('checked', all);
    });

    toggleInterestNotifyFields();
})();
