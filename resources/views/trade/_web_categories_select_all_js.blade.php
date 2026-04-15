        (function () {
            var $master = $('#trade-web-categories-select-all');
            var $grid = $('#trade-web-categories-grid');
            if (!$master.length || !$grid.length) {
                return;
            }

            var syncingFromMaster = false;
            var syncingFromGrid = false;

            function syncMaster() {
                if (syncingFromMaster) {
                    return;
                }
                var $checks = $grid.find('input[type="checkbox"]');
                var total = $checks.length;
                syncingFromGrid = true;
                if (!total) {
                    $master.iCheck('uncheck');
                    syncingFromGrid = false;
                    return;
                }
                var n = $checks.filter(function () {
                    return $(this).prop('checked');
                }).length;
                if (n === total) {
                    $master.iCheck('check');
                } else {
                    $master.iCheck('uncheck');
                }
                syncingFromGrid = false;
            }

            $master.on('ifChanged', function () {
                if (syncingFromGrid) {
                    return;
                }
                syncingFromMaster = true;
                var on = $master.prop('checked');
                $grid.find('input[type="checkbox"]').each(function () {
                    if (on) {
                        $(this).iCheck('check');
                    } else {
                        $(this).iCheck('uncheck');
                    }
                });
                syncingFromMaster = false;
            });

            $grid.find('input[type="checkbox"]').on('ifChanged', function () {
                if (syncingFromMaster) {
                    return;
                }
                syncMaster();
            });
            syncMaster();
        })();
