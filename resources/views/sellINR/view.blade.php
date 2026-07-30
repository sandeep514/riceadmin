@extends('layouts.main')

@section('content')
<style>
    .sell-query-view .detail-table th {
        width: 220px;
        background: #f7f7f7;
        vertical-align: middle;
    }
    .sell-query-view .media-card {
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        padding: 12px;
        margin-bottom: 15px;
        background: #fff;
        min-height: 220px;
    }
    .sell-query-view .media-card img {
        max-width: 100%;
        max-height: 180px;
        display: block;
        margin: 0 auto 10px;
        object-fit: contain;
    }
    .sell-query-view .media-card .media-label {
        font-weight: 600;
        margin-bottom: 8px;
    }
    .sell-query-view .media-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .sell-query-view .muted {
        color: #888;
        font-size: 12px;
    }
</style>
<div class="content-wrapper sell-query-view">
    <section class="content-header">
        <h1>
            Sell Query
            <small>View #{{ $query->id }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('master.list.sell.queries.INR') }}">Sell Query INR</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        @if (session('error'))
            @php $parts = explode('|', session('error'), 2); @endphp
            <div class="alert alert-danger">{{ $parts[1] ?? $parts[0] }}</div>
        @endif
        @if (session('success'))
            @php $parts = explode('|', session('success'), 2); @endphp
            <div class="alert alert-success">{{ $parts[1] ?? $parts[0] }}</div>
        @endif
        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="row" style="margin-bottom: 12px;">
            <div class="col-md-12">
                <a href="{{ route('master.list.sell.queries.INR') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                @if(collect($mediaFiles)->contains(fn ($f) => $f['exists']))
                    <a href="{{ route('master.download.sell.query.files', $query->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-download"></i> Download all images (ZIP)
                    </a>
                @endif
                @if((int) $query->status === 1)
                    <a href="{{ route('convert.to.trade.queries', ['type' => 'sell', 'id' => $query->id]) }}" class="btn btn-success btn-sm">
                        Convert to trade
                    </a>
                    <a href="{{ route('move.to.trade.sell.queries', $query->id) }}" class="btn btn-info btn-sm">
                        Moved to trade
                    </a>
                    <a href="{{ route('close.sell.queries', $query->id) }}" class="btn btn-danger btn-sm"
                       onclick="return confirm('Close this sell query?');">
                        Close deal
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
                                    <td>{{ \App\SellQueriesINR::$status[$query->status] ?? $query->status }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $query->type ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Quality Type</th>
                                    <td>{{ $query->RiceQualityRiceNames->type ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Quality</th>
                                    <td>{{ $query->RiceQualityRiceNames->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Quality Form</th>
                                    <td>{{ $query->RiceFormMilestone3->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Grade</th>
                                    <td>
                                        {{ trim(($query->riceGrade?->getWandType?->type ?? '') . ' ' . ($query->riceGrade?->value ?? '')) ?: '—' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Packing</th>
                                    <td>
                                        @if($query->RicePacking)
                                            {{ $query->RicePacking->packing }}{{ $query->RicePacking->description ? ' — ' . $query->RicePacking->description : '' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ $query->quantity ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Ex-factory Price</th>
                                    <td>{{ $query->offerPrice ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Valid Days / Validity</th>
                                    <td>{{ $query->validDays ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Farming</th>
                                    <td>
                                        @php
                                            $farmingRaw = $query->farming;
                                            $farmingLabel = $farmingRaw;
                                            if (is_numeric($farmingRaw)) {
                                                $farmingLabel = \App\TradeQueriesINR::$farmingTypeWeb[(int) $farmingRaw]
                                                    ?? \App\TradeQueriesINR::$farmingType[(int) $farmingRaw]
                                                    ?? $farmingRaw;
                                            }
                                        @endphp
                                        {{ $farmingLabel !== null && $farmingLabel !== '' ? $farmingLabel : '—' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Warehouse Location</th>
                                    <td>{{ $query->warehouselocation ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Person</th>
                                    <td>{{ $query->contactperson ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Phone</th>
                                    <td>{{ $query->contactMobile ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Remarks</th>
                                    <td style="white-space: pre-wrap;">{{ $query->remarks ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>
                                        {{ $query->created_at ? \Carbon\Carbon::parse($query->created_at)->format('d-m-Y H:i') : '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Submitted by (user)</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered detail-table">
                            <tbody>
                                <tr>
                                    <th>User ID</th>
                                    <td>{{ $query->created_by ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $query->UserDetail->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile</th>
                                    <td>{{ $query->UserDetail->mobile ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $query->UserDetail->email ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>User From</th>
                                    <td>{{ $query->UserDetail->user_from ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Update remarks</h3>
                    </div>
                    <div class="box-body">
                        <form method="POST" action="{{ route('master.update.remarks.saleOrder') }}">
                            @csrf
                            <input type="hidden" name="saleId" value="{{ $query->id }}">
                            <div class="form-group">
                                <textarea name="remarks" class="form-control" rows="4" placeholder="Admin remarks">{{ $query->remarks }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm">Save remarks</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Images &amp; files</h3>
                        @if(collect($mediaFiles)->contains(fn ($f) => $f['exists']))
                            <div class="box-tools pull-right">
                                <a href="{{ route('master.download.sell.query.files', $query->id) }}" class="btn btn-success btn-xs">
                                    <i class="fa fa-download"></i> Download all
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="box-body">
                        @php $anyMedia = false; @endphp
                        @foreach($mediaFiles as $media)
                            @if($media['filename'])
                                @php $anyMedia = true; @endphp
                                <div class="media-card">
                                    <div class="media-label">{{ $media['label'] }}</div>
                                    @if($media['exists'] && $media['url'])
                                        @php
                                            $ext = strtolower(pathinfo($media['filename'], PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
                                        @endphp
                                        @if($isImage)
                                            <a href="{{ $media['url'] }}" target="_blank" rel="noopener">
                                                <img src="{{ $media['url'] }}" alt="{{ $media['label'] }}">
                                            </a>
                                        @else
                                            <p class="text-center">
                                                <i class="fa fa-file-o fa-3x"></i><br>
                                                <span class="muted">{{ $media['filename'] }}</span>
                                            </p>
                                        @endif
                                        <div class="media-actions">
                                            <a href="{{ route('master.download.sell.query.file', ['sellQueryId' => $query->id, 'field' => $media['field']]) }}"
                                               class="btn btn-primary btn-xs">
                                                <i class="fa fa-download"></i> Download
                                            </a>
                                            <a href="{{ $media['url'] }}" target="_blank" rel="noopener" class="btn btn-default btn-xs">
                                                <i class="fa fa-external-link"></i> Open
                                            </a>
                                        </div>
                                    @else
                                        <p class="text-center text-muted">
                                            File recorded but missing on server.<br>
                                            <span class="muted">{{ $media['filename'] }}</span>
                                        </p>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if(! $anyMedia)
                            <p class="text-muted text-center" style="margin: 20px 0;">No images uploaded for this query.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
@endsection
