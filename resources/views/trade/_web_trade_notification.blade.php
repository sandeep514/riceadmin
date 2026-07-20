@php
    $isEdit = !empty($isEdit);
    $defaultSend = $isEdit ? '0' : '1';
    $sendOld = old('trade_notify_send', $defaultSend);
    $audienceOld = old('trade_notify_audience', 'all_category');
    $roleOld = old('trade_notify_role_id', '');
@endphp
<div class="col-md-12" style="margin-bottom: 20px;padding-left: 0;">
    <div class="group-panel" style="border:1px solid #e0e0e0;border-radius:4px;padding:12px;background:#fcfcfc;">
        <label class="group-title" style="display:block;margin-bottom:10px;">User notification (Web + Mobile)</label>
        <p class="help-block" style="font-size:12px;margin-top:0;">
            Notifies users whose <strong>role</strong> and <strong>business category</strong> match the selections below.
            <br>
            Select at least one <strong>web category</strong> above when Send = Yes.
            <br>
            Delivery: <strong>Pusher/Reverb</strong> for web portal login, <strong>Firebase</strong> when the same account has a mobile app token (both if logged in on web and mobile).
            <br>
            Placeholders: <code>{trade_no}</code>, <code>{trade_type}</code>, <code>{farming_type}</code>, <code>{quality}</code>,
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
                <label>Web role (optional filter)</label>
                <select name="trade_notify_role_id" id="trade_notify_role_id" class="form-control">
                    <option value="">All roles in selected categories</option>
                    @foreach(($webNotifyRoles ?? collect()) as $role)
                        <option value="{{ $role->id }}" {{ (string) $roleOld === (string) $role->id ? 'selected' : '' }}>
                            {{ $role->role_name }}
                        </option>
                    @endforeach
                </select>
                <p class="help-block" style="font-size:12px;">Leave empty to notify every matching category user. Pick a role to limit to that role + category.</p>
            </div>

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
                        {{ (string) $audienceOld === 'all_category' ? 'checked' : '' }}> All users in selected categories (and role if chosen)
                </label>
                <label class="radio-inline">
                    <input type="radio" name="trade_notify_audience" value="selected_users" class="trade-notify-audience"
                        {{ (string) $audienceOld === 'selected_users' ? 'checked' : '' }}> Selected users only
                </label>
            </div>

            <div class="form-group" id="trade-notify-user-multiselect-wrap" style="{{ (string) $audienceOld === 'selected_users' ? '' : 'display:none;' }}">
                <label>Users (select one or more)</label>
                <select name="trade_notify_user_ids[]" id="trade_notify_user_ids" class="form-control select2"
                    multiple="multiple" style="width:100%;" data-placeholder="Load users by selecting web categories (and role) above"></select>
                <p class="help-block" style="font-size:12px;">Pick web categories first; optionally filter by role. List is web portal users for those filters.</p>
            </div>
        </div>
    </div>
</div>
