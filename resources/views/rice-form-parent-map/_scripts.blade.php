<script>
$(function(){
    var selectedChildren = @json($selectedChildIds ?? old('child_form_ids', []));

    $('#parent_form_id, #child_form_ids').select2({
        width: '100%',
    });

    function syncChildOptions() {
        var parentId = $('#parent_form_id').val();
        var current = $('#child_form_ids').val() || [];

        $('#child_form_ids option').each(function(){
            var opt = $(this);
            if (parentId && opt.val() === parentId) {
                opt.prop('disabled', true);
            } else {
                opt.prop('disabled', false);
            }
        });

        current = current.filter(function(id){ return id !== parentId; });
        $('#child_form_ids').val(current).trigger('change.select2');
    }

    if (selectedChildren.length) {
        $('#child_form_ids').val(selectedChildren.map(String)).trigger('change');
    }

    $('#parent_form_id').on('change', syncChildOptions);
    syncChildOptions();

    $('#child_check_all').on('click', function(){
        var parentId = $('#parent_form_id').val();
        var all = [];
        $('#child_form_ids option:not(:disabled)').each(function(){
            if ($(this).val()) {
                all.push($(this).val());
            }
        });
        $('#child_form_ids').val(all).trigger('change');
    });

    $('#child_uncheck_all').on('click', function(){
        $('#child_form_ids').val(null).trigger('change');
    });
});
</script>
