<script>
$(function(){
    var riceNamesUrl = window.route + '/rice-form-map/ajax/rice-names/:type';
    var formsUrl     = window.route + '/rice-form-map/ajax/forms/:type';
    var wandsUrl     = window.route + '/rice-form-map/ajax/wands/:riceNameId';
    var selectedWandIds = @json($selectedWandIds ?? old('wand_ids', []));

    function initSelect2(el, placeholder) {
        $(el).select2({ placeholder: placeholder, allowClear: true });
    }
    initSelect2('#rice_name_id', 'Select quality');
    initSelect2('#form_id', 'Select form');

    function clearWandGrid(message) {
        $('#wand_grade_grid').html(
            '<p class="text-muted" id="wand_grade_placeholder" style="margin:8px 0;">' +
            (message || 'Select a quality to load grades.') +
            '</p>'
        );
    }

    function syncCardState($input) {
        $input.closest('.wand-grade-card').toggleClass('is-checked', $input.is(':checked'));
    }

    function loadWands(riceNameId, preselect) {
        clearWandGrid('Loading grades…');
        if (!riceNameId) {
            clearWandGrid();
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
                        '<label class="wand-grade-card' + activeClass + '" title="' + $('<div>').text(label).html() + '">' +
                            '<input type="checkbox" name="wand_ids[]" value="' + id + '"' + checked + '> ' +
                            $('<div>').text(label).html() +
                        '</label>' +
                    '</div>'
                );
            });
            if (!count) {
                clearWandGrid('No grades found for this quality.');
            }
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
    });

    $('#quality_type').on('change', function() {
        var type = $(this).val();
        $('#rice_name_id').val(null).trigger('change');
        clearWandGrid();
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

    if ($('#quality_type').val() && !$('#rice_name_id option:selected').val()) {
        $('#quality_type').trigger('change');
    }
    if ($('#rice_name_id').val()) {
        loadWands($('#rice_name_id').val(), selectedWandIds);
    }
});
</script>
