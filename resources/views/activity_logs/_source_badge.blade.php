@php
    $isWeb = ((int) ($user->userType ?? 0) === 2) || ((string) ($user->user_from ?? '') === 'web');
@endphp
@if($isWeb)
    <span class="label label-primary">Web</span>
@else
    <span class="label label-info">App</span>
@endif
