@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Paddy Trades
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Paddy Trades</li>
            </ol>
        </section>

        <section class="content">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Paddy Trades</h3>
                    <div class="pull-right">
                        <span class="label label-{{ (int)($currentMarketStatus ?? 1) === 1 ? 'success' : ((int)($currentMarketStatus ?? 1) === 12 ? 'warning' : 'danger') }}" style="font-size: 13px; padding: 6px 10px;">
                            Market: {{ ucfirst($currentMarketLabel ?? 'open') }}
                        </span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-10" style="display: inline-flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                            <a href="{{ route('update.paddy.market.status', ['tradeStatus' => 1]) }}"
                               class="btn btn-success btn-sm {{ (int)($currentMarketStatus ?? 1) === 1 ? 'active' : '' }}">
                                Open Market
                            </a>
                            <a href="{{ route('update.paddy.market.status', ['tradeStatus' => 11]) }}"
                               class="btn btn-danger btn-sm {{ (int)($currentMarketStatus ?? 1) === 11 ? 'active' : '' }}">
                                Close Market
                            </a>
                            <a href="{{ route('update.paddy.market.status', ['tradeStatus' => 12]) }}"
                               class="btn btn-warning btn-sm {{ (int)($currentMarketStatus ?? 1) === 12 ? 'active' : '' }}">
                                Hold Market
                            </a>
                        </div>
                        <div class="col-md-2 text-right">
                            <a href="{{ route('create.paddy.trade') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Create Trade
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped paddy-trade-datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sell Query</th>
                                    <th>Category</th>
                                    <th>Quality</th>
                                    <th>Hand/Combined</th>
                                    <th>Packing</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Valid Days</th>
                                    <th>Location</th>
                                    <th>Contact</th>
                                    <th>Image</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Is New</th>
                                    <th>Sold At</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trades as $trade)
                                    <tr>
                                        <td>{{ $trade->id }}</td>
                                        <td>
                                            @if($trade->paddy_sell_query_id)
                                                <a href="{{ route('view.paddy.sell.query', $trade->paddy_sell_query_id) }}">
                                                    #{{ $trade->paddy_sell_query_id }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $trade->category_label }}</td>
                                        <td>{{ $trade->quality_name ?: (optional($trade->paddyQuality)->quality ?? '-') }}</td>
                                        <td>{{ $trade->hand_combined ?: '-' }}</td>
                                        <td>{{ $trade->packing_label }}</td>
                                        <td>{{ $trade->quantity }}</td>
                                        <td>{{ $trade->rate }}</td>
                                        <td>{{ $trade->valid_days }}</td>
                                        <td>{{ $trade->location }}</td>
                                        <td>
                                            {{ $trade->contact_person }}<br>
                                            <small>{{ $trade->contact_number }}</small>
                                        </td>
                                        <td>
                                            @if($trade->image)
                                                <a href="{{ $trade->image_url }}" target="_blank">
                                                    <img src="{{ $trade->image_url }}" alt="image" style="width: 50px; height: 50px; object-fit: cover;">
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($trade->user)
                                                {{ $trade->user->name ?? $trade->user->email ?? ('#'.$trade->user_id) }}
                                            @else
                                                {{ $trade->user_id ?: '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label label-{{ $trade->status_badge_class }}">
                                                {{ $trade->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('update.paddy.trade.is_new', $trade->id) }}" style="display:inline-block; min-width: 90px;">
                                                @csrf
                                                <select name="is_new" class="form-control input-sm js-paddy-is-new" onchange="this.form.submit()">
                                                    <option value="0" {{ (int) $trade->is_new === 0 ? 'selected' : '' }}>No</option>
                                                    <option value="1" {{ (int) $trade->is_new === 1 ? 'selected' : '' }}>Yes</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            @if((int) $trade->status === 3)
                                                {{ $trade->sold_at_amount ?: '-' }}
                                                @if($trade->sold_at)
                                                    <br><small>{{ \Carbon\Carbon::parse($trade->sold_at)->format('d-m-Y H:i') }}</small>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $trade->created_at ? \Carbon\Carbon::parse($trade->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                        <td style="white-space: nowrap;">
                                            <a href="{{ route('view.paddy.trade', $trade->id) }}" class="btn btn-primary btn-xs">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            @include('paddySellQuery._trade_status_actions', ['trade' => $trade])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="18" class="text-center">No paddy trades found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('paddySellQuery._trade_status_modal')
@endsection

@section('javascript')
<script>
    $(function () {
        $('.paddy-trade-datatable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [11, 14, 17] }]
        });

        var statusForm = document.getElementById('paddyTradeStatusForm');
        var statusValue = document.getElementById('paddyTradeStatusValue');
        var soldAmountHidden = document.getElementById('paddyTradeSoldAmountHidden');
        var pendingSoldTradeId = null;
        var statusUrlBase = @json(url('administrator/update/paddy/trade/status'));

        function submitStatus(tradeId, status, soldAmount) {
            statusForm.action = statusUrlBase + '/' + tradeId;
            statusValue.value = status;
            soldAmountHidden.value = soldAmount || '';
            statusForm.submit();
        }

        $(document).on('click', '.js-paddy-trade-status', function (e) {
            e.preventDefault();
            var $el = $(this);
            var tradeId = $el.data('id');
            var status = parseInt($el.data('status'), 10);
            var label = $el.data('label') || 'this status';

            if (status === 3) {
                pendingSoldTradeId = tradeId;
                $('#paddyTradeSoldIdLabel').text(tradeId);
                $('#paddyTradeSoldAmountInput').val($el.data('sold-amount') || '');
                $('#paddyTradeSoldModal').modal('show');
                return;
            }

            if (confirm('Set trade #' + tradeId + ' status to ' + label + '?')) {
                submitStatus(tradeId, status, '');
            }
        });

        $('#paddyTradeSoldConfirmBtn').on('click', function () {
            if (!pendingSoldTradeId) {
                return;
            }
            var amount = $('#paddyTradeSoldAmountInput').val() || '';
            submitStatus(pendingSoldTradeId, 3, amount);
        });
    });
</script>
@endsection
