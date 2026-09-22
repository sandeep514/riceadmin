@props([
    'userId',
    'value' => 0,
])
@php
    $isActive = (int) $value === 1;
@endphp
<form method="POST" action="{{ route('update.user.business.details.recommended', $userId) }}" style="margin:0;">
    @csrf
    <input type="hidden" name="update_field" value="is_active_listing">
    <select name="is_active_listing" class="form-control input-sm" onchange="this.form.submit()" style="min-width:110px; display:inline-block;">
        <option value="0" @selected(! $isActive)>Hidden</option>
        <option value="1" @selected($isActive)>Active</option>
    </select>
</form>
