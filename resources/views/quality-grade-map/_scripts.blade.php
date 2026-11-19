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
    initSelect2('#wand_ids', 'Select grades');

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

    function loadWands(riceNameId, preselect) {
        $('#wand_ids').val(null).trigger('change').empty();
        if (!riceNameId) {
            return;
        }
        $.get(wandsUrl.replace(':riceNameId', riceNameId), function(data) {
            $.each(data, function(id, label) {
                $('#wand_ids').append('<option value="' + id + '">' + label + '</option>');
            });
            if (preselect && preselect.length) {
                $('#wand_ids').val(preselect.map(String)).trigger('change');
            }
        });
    }

    $('#quality_type').on('change', function() {
        var type = $(this).val();
        $('#rice_name_id').val(null).trigger('change');
        $('#wand_ids').val(null).trigger('change').empty();
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

    $(document).on('click', '#wand_check_all', function() {
        var allIds = $('#wand_ids option').map(function() {
            return this.value ? String(this.value) : null;
        }).get();
        $('#wand_ids').val(allIds).trigger('change');
    });
    $(document).on('click', '#wand_uncheck_all', function() {
        $('#wand_ids').val(null).trigger('change');
    });

    if ($('#quality_type').val() && !$('#rice_name_id option:selected').val()) {
        $('#quality_type').trigger('change');
    }
    if ($('#rice_name_id').val()) {
        loadWands($('#rice_name_id').val(), selectedWandIds);
    }
});
</script>
