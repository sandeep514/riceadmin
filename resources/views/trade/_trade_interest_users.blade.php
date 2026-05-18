<div class="col-md-12" id="trade-interest-users-panel" style="margin-bottom: 20px;padding-left: 0;display:none;">
    <div class="group-panel" style="border:1px solid #d4edda;border-radius:4px;padding:12px;background:#f8fff9;">
        <label class="group-title" style="display:block;margin-bottom:10px;">Notify users with matching interests</label>
        <p class="help-block" style="font-size:12px;margin-top:0;">
            Web users who saved the same Quality, Rice Form, and Grade in their interests.
            Select who should receive a special portal notification when this trade is saved.
        </p>

        <div class="form-group">
            <label>Send interest notification</label><br>
            <label class="radio-inline">
                <input type="radio" name="trade_interest_notify_send" value="1" class="trade-interest-notify-send" checked> Yes
            </label>
            <label class="radio-inline">
                <input type="radio" name="trade_interest_notify_send" value="0" class="trade-interest-notify-send"> No
            </label>
        </div>

        <div id="trade-interest-notify-fields">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="trade_interest_notify_title" class="form-control" maxlength="500"
                    value="{{ old('trade_interest_notify_title', 'Special trade alert') }}">
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="trade_interest_notify_message" class="form-control" rows="3">{{ old('trade_interest_notify_message', \App\Services\TradeWebNotificationService::DEFAULT_TRADE_INTEREST_NOTIFY_MESSAGE) }}</textarea>
                <p class="help-block" style="font-size:12px;">
                    Placeholders: <code>{trade_no}</code>, <code>{quality}</code>, <code>{rice_form}</code>, <code>{grade}</code>
                </p>
            </div>

            <div class="form-group" style="margin-bottom:8px;">
                <label style="margin-right:12px;">Matching users</label>
                <label class="checkbox-inline" style="font-weight:normal;">
                    <input type="checkbox" id="trade-interest-select-all" checked> Select all
                </label>
                <span id="trade-interest-users-count" class="text-muted" style="font-size:12px;margin-left:8px;"></span>
            </div>

            <div id="trade-interest-users-loading" class="text-muted" style="display:none;font-size:12px;">Loading users…</div>
            <div id="trade-interest-users-empty" class="text-muted" style="display:none;font-size:12px;">No web users have this exact interest saved.</div>
            <div id="trade-interest-users-list" class="well" style="max-height:220px;overflow-y:auto;margin-bottom:0;padding:10px 12px;"></div>
        </div>
    </div>
</div>
