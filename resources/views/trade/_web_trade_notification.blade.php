@php
    $isEdit = !empty($isEdit);
    $defaultSend = $isEdit ? '0' : '1';
    $sendOld = old('trade_notify_send', $defaultSend);
    $audienceOld = old('trade_notify_audience', 'all_category');
@endphp
<div class="col-md-12" style="margin-bottom: 20px;padding-left: 0;">
    <div class="group-panel" style="border:1px solid #e0e0e0;border-radius:4px;padding:12px;background:#fcfcfc;">
        <label class="group-title" style="display:block;margin-bottom:10px;">Web user notification</label>
        <p class="help-block" style="font-size:12px;margin-top:0;">
            Notifies web portal users whose business category matches the web categories selected above.
            You can use placeholders, filled automatically when the trade is saved:
            <code>{trade_no}</code>, <code>{trade_type}</code>, <code>{farming_type}</code>, <code>{quality}</code>,
            <code>{rice_form}</code>, <code>{grade}</code>, <code>{quantity}</code>.
        </p>

        <div class="form-group">
            <label>Send notification</label><br>
            <label class="radio-inline">
                <input type="radio" name="trade_notify_send" value="1" class="trade-notify-send"
                    {{ (string) $sendOld === '1' ? 'checked' : '' }}> Yes
            </label>
            <label class="radio-inline">
                <input type="radio" name="trade_notify_send" value="0" class="trade-notify-send"
                    {{ (string) $sendOld === '0' ? 'checked' : '' }}> No
            </label>
        </div>

        <div id="trade-notify-fields" style="{{ (string) $sendOld === '1' ? '' : 'display:none;' }}">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="trade_notify_title" class="form-control" maxlength="500"
                    value="{{ old('trade_notify_title', $isEdit ? '' : 'New Trade alert') }}"
                    placeholder="New Trade alert">
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="trade_notify_message" class="form-control" rows="10"
                    placeholder="Trade details...">{{ old('trade_notify_message', \App\Services\TradeWebNotificationService::DEFAULT_TRADE_NOTIFY_MESSAGE) }}</textarea>
            </div>

            <div class="form-group">
                <label>Recipients</label><br>
                <label class="radio-inline">
                    <input type="radio" name="trade_notify_audience" value="all_category" class="trade-notify-audience"
                        {{ (string) $audienceOld === 'all_category' ? 'checked' : '' }}> All web users in selected categories
                </label>
                <label class="radio-inline">
                    <input type="radio" name="trade_notify_audience" value="selected_users" class="trade-notify-audience"
                        {{ (string) $audienceOld === 'selected_users' ? 'checked' : '' }}> Selected users only
                </label>
            </div>

            <div class="form-group" id="trade-notify-user-multiselect-wrap" style="{{ (string) $audienceOld === 'selected_users' ? '' : 'display:none;' }}">
                <label>Users (select one or more)</label>
                <select name="trade_notify_user_ids[]" id="trade_notify_user_ids" class="form-control select2"
                    multiple="multiple" style="width:100%;" data-placeholder="Load users by selecting web categories above"></select>
                <p class="help-block" style="font-size:12px;">Pick web categories first; the list includes users linked to those categories.</p>
            </div>
        </div>
    </div>
</div>
