@extends('layouts.main')

@section('content')
<style>
    .paddy-sell-view .detail-table th {
        width: 220px;
        background: #f7f7f7;
        vertical-align: middle;
    }
    .paddy-sell-view .media-card {
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        padding: 12px;
        margin-bottom: 15px;
        background: #fff;
        text-align: center;
    }
    .paddy-sell-view .media-card img {
        max-width: 100%;
        max-height: 280px;
        display: block;
        margin: 0 auto 10px;
        object-fit: contain;
    }
</style>
<div class="content-wrapper paddy-sell-view">
    <section class="content-header">
        <h1>
            Paddy Sell Query
            <small>View #{{ $query->id }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('list.paddy.sell.queries') }}">Paddy Sell Queries</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom: 12px;">
            <div class="col-md-12">
                <a href="{{ route('list.paddy.sell.queries') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                @if($query->image)
                    <a href="{{ route('download.paddy.sell.query.image', $query->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-download"></i> Download image
                    </a>
                @endif
                @if((int) $query->status === 1)
                    <a href="{{ route('convert.paddy.sell.query', $query->id) }}"
                       class="btn btn-success btn-sm">
                        <i class="fa fa-exchange"></i> Convert to paddy trade
                    </a>
                    <a href="{{ route('close.paddy.sell.query', $query->id) }}"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Close this paddy sell query?')">
                        Close
                    </a>
                @elseif((int) $query->status === 2 && optional($query->paddyTrade)->id)
                    <a href="{{ route('view.paddy.trade', $query->paddyTrade->id) }}"
                       class="btn btn-info btn-sm">
                        <i class="fa fa-link"></i> View paddy trade
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Query details</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered detail-table">
                            <tbody>
                                <tr>
                                    <th>Query ID</th>
                                    <td>{{ $query->id }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if((int) $query->status === 1)
                                            <span class="label label-warning">Pending</span>
                                        @elseif((int) $query->status === 2)
                                            <span class="label label-success">Converted to trade</span>
                                        @else
                                            <span class="label label-default">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $query->type ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Category (Rice Type)</th>
                                    <td>{{ $query->category_label }}</td>
                                </tr>
                                <tr>
                                    <th>Quality</th>
                                    <td>
                                        {{ $query->quality_name ?: (optional($query->paddyQuality)->quality ?? '-') }}
                                        @if($query->quality)
                                            <small class="text-muted">(ID: {{ $query->quality }})</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Hand / Combined</th>
                                    <td>{{ $query->hand_combined ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Packing</th>
                                    <td>{{ $query->packing ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ $query->quantity ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Rate</th>
                                    <td>{{ $query->rate ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Valid Days</th>
                                    <td>{{ $query->valid_days ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td>{{ $query->location ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Person</th>
                                    <td>{{ $query->contact_person ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Number</th>
                                    <td>{{ $query->contact_number ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>
                                        @if($query->user)
                                            {{ $query->user->name ?? $query->user->email ?? ('#'.$query->user_id) }}
                                            <small class="text-muted">(ID: {{ $query->user_id }})</small>
                                        @else
                                            {{ $query->user_id ?: '-' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $query->created_at ? \Carbon\Carbon::parse($query->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $query->updated_at ? \Carbon\Carbon::parse($query->updated_at)->format('d-m-Y H:i') : '-' }}</td>
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
                        @if($query->image)
                            <div class="media-card">
                                <a href="{{ $query->image_url }}" target="_blank">
                                    <img src="{{ $query->image_url }}" alt="Paddy sell image">
                                </a>
                                <a href="{{ route('download.paddy.sell.query.image', $query->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-download"></i> Download image
                                </a>
                            </div>
                        @else
                            <p class="text-muted text-center">No image uploaded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
