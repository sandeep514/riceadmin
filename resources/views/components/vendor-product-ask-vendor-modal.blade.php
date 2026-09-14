<div class="modal fade" id="vendorProductAskVendorModal" tabindex="-1" role="dialog" aria-labelledby="vendorProductAskVendorLabel">
    <div class="modal-dialog" role="document">
        <form method="POST" id="vendorProductAskVendorForm" action="#">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="vendorProductAskVendorLabel">Message for vendor</h4>
                </div>
                <div class="modal-body">
                    <p>Write why you are contacting the vendor. This message will be emailed to them, and the product will stay off the live listing until you verify it again.</p>
                    <div class="form-group">
                        <label for="vendorProductAskVendorMessage">Message <span class="text-danger">*</span></label>
                        <textarea id="vendorProductAskVendorMessage"
                                  name="message"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  required
                                  placeholder="Tell the vendor what needs to be updated"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send to vendor</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(function () {
        var $modal = $('#vendorProductAskVendorModal');
        var $form = $('#vendorProductAskVendorForm');
        var $message = $('#vendorProductAskVendorMessage');

        $(document).on('click', '.js-vendor-product-ask-vendor', function () {
            $form.attr('action', $(this).data('action'));
            $message.val('');
            $modal.modal('show');
            setTimeout(function () { $message.focus(); }, 300);
        });

        $form.on('submit', function () {
            var value = $.trim($message.val() || '');
            if (value.length < 3) {
                alert('Please enter a message (at least 3 characters).');
                $message.focus();
                return false;
            }
            return true;
        });
    });
</script>
