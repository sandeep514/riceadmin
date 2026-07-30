(function () {
    function revokePreviewUrls($preview) {
        $preview.find('img, video').each(function () {
            var src = this.getAttribute('src');
            if (src && src.indexOf('blob:') === 0) {
                try { URL.revokeObjectURL(src); } catch (e) {}
            }
        });
    }

    function showLocalPreview($field, file) {
        var $preview = $field.find('.trade-media-preview').first();
        if (!$preview.length || !file) {
            return;
        }
        revokePreviewUrls($preview);
        $preview.empty().hide();

        var url = URL.createObjectURL(file);
        if ((file.type || '').indexOf('video/') === 0) {
            $preview.html(
                '<video src="' + url + '" controls style="max-width:320px;width:100%;"></video>' +
                '<div class="text-muted" style="font-size:12px;margin-top:4px;">Selected: ' + $('<div>').text(file.name).html() + '</div>'
            ).show();
        } else if ((file.type || '').indexOf('image/') === 0 || !file.type) {
            $preview.html(
                '<img src="' + url + '" alt="Preview" style="max-width:200px;width:100%;height:auto;" />' +
                '<div class="text-muted" style="font-size:12px;margin-top:4px;">Selected: ' + $('<div>').text(file.name).html() + '</div>'
            ).show();
        } else {
            $preview.html(
                '<div class="text-muted" style="font-size:12px;">Selected: ' + $('<div>').text(file.name).html() + '</div>'
            ).show();
        }
    }

    function clearField($field) {
        var $input = $field.find('.trade-media-input').first();
        var $preview = $field.find('.trade-media-preview').first();
        if ($input.length) {
            $input.val('');
        }
        if ($preview.length) {
            revokePreviewUrls($preview);
            $preview.empty().hide();
        }
    }

    $(document).on('click', '.trade-media-clear', function (e) {
        e.preventDefault();
        clearField($(this).closest('.trade-media-field'));
    });

    $(document).on('change', '.trade-media-input', function () {
        var $field = $(this).closest('.trade-media-field');
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            var $preview = $field.find('.trade-media-preview').first();
            revokePreviewUrls($preview);
            $preview.empty().hide();
            return;
        }
        // New upload replaces remove intent for this slot.
        $field.find('.trade-media-remove').prop('checked', false);
        $field.find('.trade-media-existing').show().css('opacity', 1);
        showLocalPreview($field, file);
    });

    $(document).on('change ifChanged', '.trade-media-remove', function () {
        var $existing = $(this).closest('.trade-media-existing');
        var $field = $(this).closest('.trade-media-field');
        if ($(this).is(':checked')) {
            $existing.css('opacity', 0.4);
            // Clearing a new selection if they chose remove existing
            clearField($field);
            // restore remove checkbox after clearField doesn't touch it
            $(this).prop('checked', true);
            $existing.css('opacity', 0.4);
        } else {
            $existing.css('opacity', 1);
        }
    });
})();
