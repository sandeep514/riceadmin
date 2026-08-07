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
                        <div class="col-md-12" style="display: inline-flex; gap: 8px; flex-wrap: wrap; align-items: center;">
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
                                            @if((int) $trade->status === 1)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-default">Closed</span>
                                            @endif
                                        </td>
                                        <td>{{ $trade->created_at ? \Carbon\Carbon::parse($trade->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                        <td style="white-space: nowrap;">
                                            <a href="{{ route('view.paddy.trade', $trade->id) }}" class="btn btn-primary btn-xs">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            @if((int) $trade->status === 1)
                                                <a href="{{ route('close.paddy.trade', $trade->id) }}"
                                                   class="btn btn-danger btn-xs"
                                                   onclick="return confirm('Close this paddy trade?')">
                                                    Close
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="16" class="text-center">No paddy trades found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(function () {
        $('.paddy-trade-datatable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [11, 15] }]
        });
    });
</script>
@endsection
