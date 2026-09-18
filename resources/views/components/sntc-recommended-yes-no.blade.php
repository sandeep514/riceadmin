@props([
    'userId',
    'value' => 0,
])
@php
    $isYes = (int) $value === 1;
@endphp
<form method="POST" action="{{ route('update.user.business.details.recommended', $userId) }}" style="margin:0;">
    @csrf
    <input type="hidden" name="update_field" value="is_sntc_recommended">
    <select name="is_sntc_recommended" class="form-control input-sm" onchange="this.form.submit()" style="min-width:90px; display:inline-block;">
        <option value="0" @selected(! $isYes)>No</option>
        <option value="1" @selected($isYes)>Yes</option>
    </select>
</form>
