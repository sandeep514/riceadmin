@extends('layouts.main')

@section('content')
<style>
    .paddy-trade-view .detail-table th {
        width: 220px;
        background: #f7f7f7;
        vertical-align: middle;
    }
    .paddy-trade-view .media-card {
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        padding: 12px;
        text-align: center;
    }
    .paddy-trade-view .media-card img {
        max-width: 100%;
        max-height: 280px;
        display: block;
        margin: 0 auto 10px;
        object-fit: contain;
    }
</style>
<div class="content-wrapper paddy-trade-view">
    <section class="content-header">
        <h1>
            Paddy Trade
            <small>View #{{ $trade->id }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('list.paddy.trades') }}">Paddy Trades</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom: 12px;">
            <div class="col-md-12" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                <a href="{{ route('list.paddy.trades') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                @if($trade->paddy_sell_query_id)
                    <a href="{{ route('view.paddy.sell.query', $trade->paddy_sell_query_id) }}" class="btn btn-info btn-sm">
                        <i class="fa fa-link"></i> Source sell query
                    </a>
                @endif
                @include('paddySellQuery._trade_status_actions', ['trade' => $trade])
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Trade details</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered detail-table">
                            <tbody>
                                <tr>
                                    <th>Trade ID</th>
                                    <td>{{ $trade->id }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="label label-{{ $trade->status_badge_class }}">
                                            {{ $trade->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @if((int) $trade->status === 3)
                                    <tr>
                                        <th>Sold at amount</th>
                                        <td>{{ $trade->sold_at_amount ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Sold at</th>
                                        <td>{{ $trade->sold_at ? \Carbon\Carbon::parse($trade->sold_at)->format('d-m-Y H:i') : '-' }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Source Sell Query</th>
                                    <td>
                                        @if($trade->paddy_sell_query_id)
                                            <a href="{{ route('view.paddy.sell.query', $trade->paddy_sell_query_id) }}">
                                                #{{ $trade->paddy_sell_query_id }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>{{ $trade->category_label }}</td>
                                </tr>
                                <tr>
                                    <th>Quality</th>
                                    <td>{{ $trade->quality_name ?: (optional($trade->paddyQuality)->quality ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <th>Hand / Combined</th>
                                    <td>{{ $trade->hand_combined ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Packing</th>
                                    <td>{{ $trade->packing_label }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ $trade->quantity ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Rate</th>
                                    <td>{{ $trade->rate ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Valid Days</th>
                                    <td>{{ $trade->valid_days ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td>{{ $trade->location ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Person</th>
                                    <td>{{ $trade->contact_person ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Number</th>
                                    <td>{{ $trade->contact_number ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $trade->type ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Remarks</th>
                                    <td>{{ $trade->remarks ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Seller User</th>
                                    <td>
                                        @if($trade->user)
                                            {{ $trade->user->name ?? $trade->user->email ?? ('#'.$trade->user_id) }}
                                            <small class="text-muted">(ID: {{ $trade->user_id }})</small>
                                        @else
                                            {{ $trade->user_id ?: '-' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Converted By</th>
                                    <td>
                                        @if($trade->creator)
                                            {{ $trade->creator->name ?? $trade->creator->email ?? ('#'.$trade->created_by) }}
                                        @else
                                            {{ $trade->created_by ?: '-' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $trade->created_at ? \Carbon\Carbon::parse($trade->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Image</h3>
                    </div>
                    <div class="box-body">
                        @if($trade->image)
                            <div class="media-card">
                                <a href="{{ $trade->image_url }}" target="_blank">
                                    <img src="{{ $trade->image_url }}" alt="Paddy trade image">
                                </a>
                            </div>
                        @else
                            <p class="text-muted text-center">No image.</p>
                        @endif
                    </div>
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
