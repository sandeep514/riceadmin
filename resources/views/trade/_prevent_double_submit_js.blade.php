// Prevent double-submit on Save/Update Trade (only runs after HTML5 validation passes).
(function () {
    $(document).on('submit', '.box-primary form', function (e) {
        var $form = $(this);
        var $btn = $form.find('button[type="submit"].js-trade-submit, button[type="submit"].btn-primary').first();
        if (!$btn.length) {
            return;
        }
        if ($form.data('trade-submitting')) {
            e.preventDefault();
            return false;
        }
        $form.data('trade-submitting', true);
        var original = $btn.data('original-text') || $btn.text();
        $btn.data('original-text', original);
        $btn.prop('disabled', true).text('Please wait...');
    });
})();
