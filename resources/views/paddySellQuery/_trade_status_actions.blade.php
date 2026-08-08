@php
    $status = (int) $trade->status;
    $statusLabels = \App\PaddyTrade::$statusLabels;
@endphp
<div class="btn-group">
    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        Status <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right" role="menu">
        <li class="{{ $status === 1 ? 'active' : '' }}">
            <a href="javascript:void(0)"
               class="js-paddy-trade-status"
               data-id="{{ $trade->id }}"
               data-status="1"
               data-label="Active">
                Active
            </a>
        </li>
        <li class="{{ $status === 4 ? 'active' : '' }}">
            <a href="javascript:void(0)"
               class="js-paddy-trade-status"
               data-id="{{ $trade->id }}"
               data-status="4"
               data-label="In-Process">
                In-Process
            </a>
        </li>
        <li class="{{ $status === 12 ? 'active' : '' }}">
            <a href="javascript:void(0)"
               class="js-paddy-trade-status"
               data-id="{{ $trade->id }}"
               data-status="12"
               data-label="Hold">
                Hold
            </a>
        </li>
        <li class="{{ $status === 3 ? 'active' : '' }}">
            <a href="javascript:void(0)"
               class="js-paddy-trade-status js-paddy-trade-sold"
               data-id="{{ $trade->id }}"
               data-status="3"
               data-label="Sold"
               data-sold-amount="{{ $trade->sold_at_amount }}">
                Sold
            </a>
        </li>
    </ul>
</div>
