{{-- Shared sold-amount modal + status form for paddy trades --}}
<form id="paddyTradeStatusForm" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="status" id="paddyTradeStatusValue" value="">
    <input type="hidden" name="sold_at_amount" id="paddyTradeSoldAmountHidden" value="">
</form>

<div class="modal fade" id="paddyTradeSoldModal" tabindex="-1" role="dialog" aria-labelledby="paddyTradeSoldModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="paddyTradeSoldModalLabel">Mark trade as Sold</h4>
            </div>
            <div class="modal-body">
                <p>Trade #<strong id="paddyTradeSoldIdLabel"></strong> will be marked as <strong>Sold</strong>.</p>
                <div class="form-group">
                    <label for="paddyTradeSoldAmountInput">Sold at amount <small class="text-muted">(optional)</small></label>
                    <input type="text"
                           class="form-control"
                           id="paddyTradeSoldAmountInput"
                           placeholder="e.g. 54500"
                           autocomplete="off">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="paddyTradeSoldConfirmBtn">
                    Mark as Sold
                </button>
            </div>
        </div>
    </div>
</div>
