<div class="modal fade" id="vendorProductDeactivateModal" tabindex="-1" role="dialog" aria-labelledby="vendorProductDeactivateLabel">
    <div class="modal-dialog" role="document">
        <form method="POST" id="vendorProductDeactivateForm" action="#">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="vendorProductDeactivateLabel">De-activate product</h4>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason. This message will be emailed to the vendor.</p>
                    <div class="form-group">
                        <label for="vendorProductDeactivateReason">Reason <span class="text-danger">*</span></label>
                        <textarea id="vendorProductDeactivateReason"
                                  name="reason"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  required
                                  placeholder="Explain why this product is being de-activated"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">De-activate &amp; notify vendor</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(function () {
        var $modal = $('#vendorProductDeactivateModal');
        var $form = $('#vendorProductDeactivateForm');
        var $reason = $('#vendorProductDeactivateReason');

        $(document).on('click', '.js-vendor-product-deactivate', function () {
            var action = $(this).data('action');
            $form.attr('action', action);
            $reason.val('');
            $modal.modal('show');
            setTimeout(function () { $reason.focus(); }, 300);
        });

        $form.on('submit', function () {
            var value = $.trim($reason.val() || '');
            if (value.length < 3) {
                alert('Please enter a reason (at least 3 characters).');
                $reason.focus();
                return false;
            }
            return true;
        });
    });
</script>
