<script>
$(function(){
    var riceNamesUrl = window.route + '/rice-form-map/ajax/rice-names/:type';
    var formsUrl     = window.route + '/rice-form-map/ajax/forms/:type';
    var wandsUrl     = window.route + '/rice-form-map/ajax/wands/:riceNameId';
    var usedWandsUrl = window.route + '/quality-grade-map/ajax/used-wands';
    var selectedWandIds = @json($selectedWandIds ?? old('wand_ids', []));
    var usedWandIds = [];

    function initSelect2(el, placeholder) {
        $(el).select2({ placeholder: placeholder, allowClear: true });
    }
    initSelect2('#rice_name_id', 'Select quality');
    initSelect2('#form_id', 'Select form');

    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function clearWandGrid(message) {
        $('#wand_grade_grid').html(
            '<p class="text-muted" id="wand_grade_placeholder" style="margin:8px 0;">' +
            escapeHtml(message || 'Select a quality to load grades.') +
            '</p>'
        );
    }

    function resetUsedPanel(message) {
        usedWandIds = [];
        $('#used_wands_list').empty().hide();
        $('#used_wands_empty').hide();
        $('#used_wands_placeholder').text(message || 'Select quality and form to see grades already used on trades / sell queries.').show();
    }

    function syncCardState($input) {
        var $card = $input.closest('.wand-grade-card');
        $card.toggleClass('is-checked', $input.is(':checked'));
        var id = String($input.val());
        $('#used_wands_list .used-wand-btn[data-id="' + id + '"]').toggleClass('is-selected', $input.is(':checked'));
    }

    function markUsedHints() {
        $('#wand_grade_grid .wand-grade-card').each(function() {
            var id = String($(this).find('input').val());
            $(this).toggleClass('is-used-hint', usedWandIds.indexOf(id) !== -1);
        });
        $('#used_wands_list .used-wand-btn').each(function() {
            var id = String($(this).data('id'));
            var $cb = $('#wand_grade_grid input[type="checkbox"][value="' + id + '"]');
            $(this).toggleClass('is-selected', $cb.length && $cb.is(':checked'));
        });
    }

    function renderUsedWands(wands) {
        usedWandIds = (wands || []).map(function(w) { return String(w.id); });
        $('#used_wands_placeholder').hide();
        $('#used_wands_empty').hide();
        var $list = $('#used_wands_list').empty();

        if (!usedWandIds.length) {
            $list.hide();
            $('#used_wands_empty').show();
            markUsedHints();
            return;
        }

        $.each(wands, function(_, wand) {
            $list.append(
                '<li>' +
                    '<button type="button" class="used-wand-btn" data-id="' + wand.id + '" title="' + escapeHtml(wand.label) + '">' +
                        '<i class="fa fa-check-square-o"></i> ' + escapeHtml(wand.label) +
                    '</button>' +
                '</li>'
            );
        });
        $list.show();
        markUsedHints();
    }

    function loadUsedWands() {
        var riceNameId = $('#rice_name_id').val();
        var formId = $('#form_id').val();

        if (!riceNameId || !formId) {
            resetUsedPanel(
                !riceNameId && !formId
                    ? 'Select quality and form to see grades already used on trades / sell queries.'
                    : (!formId ? 'Select a form to see grades already used for this quality.' : 'Select a quality to see grades already used for this form.')
            );
            markUsedHints();
            return;
        }

        resetUsedPanel('Loading…');
        $.get(usedWandsUrl, { rice_name_id: riceNameId, form_id: formId })
            .done(function(resp) {
                renderUsedWands((resp && resp.wands) ? resp.wands : []);
            })
            .fail(function() {
                resetUsedPanel('Could not load used grades. Try again.');
            });
    }

    function loadWands(riceNameId, preselect) {
        clearWandGrid('Loading grades…');
        if (!riceNameId) {
            clearWandGrid();
            markUsedHints();
            return;
        }
        var selected = (preselect || []).map(String);
        $.get(wandsUrl.replace(':riceNameId', riceNameId), function(data) {
            var $grid = $('#wand_grade_grid').empty();
            var count = 0;
            $.each(data, function(id, label) {
                count++;
                var checked = selected.indexOf(String(id)) !== -1 ? ' checked' : '';
                var activeClass = checked ? ' is-checked' : '';
                $grid.append(
                    '<div class="wand-grade-item">' +
                        '<label class="wand-grade-card' + activeClass + '" title="' + escapeHtml(label) + '">' +
                            '<input type="checkbox" name="wand_ids[]" value="' + id + '"' + checked + '> ' +
                            escapeHtml(label) +
                        '</label>' +
                    '</div>'
                );
            });
            if (!count) {
                clearWandGrid('No grades found for this quality.');
            }
            markUsedHints();
        }).fail(function() {
            clearWandGrid('Could not load grades. Try again.');
        });
    }

    $.get(formsUrl.replace(':type', 'all'), function(data) {
        var currentForm = $('#form_id').val();
        $('#form_id').empty().append('<option value="">-- Select rice form --</option>');
        $.each(data, function(id, name) {
            $('#form_id').append('<option value="' + id + '">' + name + '</option>');
        });
        if (currentForm) {
            $('#form_id').val(currentForm).trigger('change.select2');
        }
        loadUsedWands();
    });

    $('#quality_type').on('change', function() {
        var type = $(this).val();
        $('#rice_name_id').val(null).trigger('change');
        clearWandGrid();
        resetUsedPanel();
        $('#rice_name_id').empty().append('<option value="">-- Select quality --</option>');
        if (!type) {
            return;
        }
        $.get(riceNamesUrl.replace(':type', type), function(data) {
            $.each(data, function(id, name) {
                $('#rice_name_id').append('<option value="' + id + '">' + name + '</option>');
            });
            $('#rice_name_id').trigger('change.select2');
        });
    });

    $('#rice_name_id').on('change', function() {
        loadWands($(this).val(), []);
        loadUsedWands();
    });

    $('#form_id').on('change', function() {
        loadUsedWands();
    });

    $(document).on('change', '#wand_grade_grid input[type="checkbox"]', function() {
        syncCardState($(this));
    });

    $(document).on('click', '#wand_check_all', function() {
        $('#wand_grade_grid input[type="checkbox"]').prop('checked', true).each(function() {
            syncCardState($(this));
        });
    });
    $(document).on('click', '#wand_uncheck_all', function() {
        $('#wand_grade_grid input[type="checkbox"]').prop('checked', false).each(function() {
            syncCardState($(this));
        });
    });

    $(document).on('click', '#used_wands_list .used-wand-btn', function() {
        var id = String($(this).data('id'));
        var $cb = $('#wand_grade_grid input[type="checkbox"][value="' + id + '"]');
        if (!$cb.length) {
            return;
        }
        $cb.prop('checked', true);
        syncCardState($cb);
        var $card = $cb.closest('.wand-grade-card')[0];
        if ($card && $card.scrollIntoView) {
            $card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });

    if ($('#quality_type').val() && !$('#rice_name_id option:selected').val()) {
        $('#quality_type').trigger('change');
    }
    if ($('#rice_name_id').val()) {
        loadWands($('#rice_name_id').val(), selectedWandIds);
    }
    loadUsedWands();
});
</script>
