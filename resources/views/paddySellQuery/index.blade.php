@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Paddy Sell Queries
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Paddy Sell Queries</li>
            </ol>
        </section>

        <section class="content">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Paddy Sell Queries</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped paddy-sell-query-datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Quality</th>
                                    <th>Hand/Combined</th>
                                    <th>Packing</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Valid Days</th>
                                    <th>Location</th>
                                    <th>Contact Person</th>
                                    <th>Contact Number</th>
                                    <th>Image</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($queries as $query)
                                    <tr>
                                        <td>{{ $query->id }}</td>
                                        <td>{{ $query->category_label }}</td>
                                        <td>
                                            {{ $query->quality_name ?: (optional($query->paddyQuality)->quality ?? '-') }}
                                            @if($query->quality)
                                                <small class="text-muted">(ID: {{ $query->quality }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $query->hand_combined ?: '-' }}</td>
                                        <td>{{ $query->packing ?: '-' }}</td>
                                        <td>{{ $query->quantity }}</td>
                                        <td>{{ $query->rate }}</td>
                                        <td>{{ $query->valid_days }}</td>
                                        <td>{{ $query->location }}</td>
                                        <td>{{ $query->contact_person }}</td>
                                        <td>{{ $query->contact_number }}</td>
                                        <td>
                                            @if($query->image)
                                                <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                                                    <a href="{{ $query->image_url }}" target="_blank">
                                                        <img src="{{ $query->image_url }}" alt="image" style="width: 60px; height: 60px; object-fit: cover;">
                                                    </a>
                                                    <a href="{{ route('download.paddy.sell.query.image', $query->id) }}"
                                                       class="btn btn-default btn-xs"
                                                       title="Download image">
                                                        <i class="fa fa-download"></i> Download
                                                    </a>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($query->user)
                                                {{ $query->user->name ?? $query->user->email ?? ('#'.$query->user_id) }}
                                            @else
                                                {{ $query->user_id ?: '-' }}
                                            @endif
                                        </td>
                                        <td>{{ $query->type }}</td>
                                        <td>
                                            @if((int) $query->status === 1)
                                                <span class="label label-warning">Pending</span>
                                            @elseif((int) $query->status === 2)
                                                <span class="label label-success">Converted to trade</span>
                                            @else
                                                <span class="label label-default">Closed</span>
                                            @endif
                                        </td>
                                        <td>{{ $query->created_at ? \Carbon\Carbon::parse($query->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                        <td style="white-space: nowrap;">
                                            <a href="{{ route('view.paddy.sell.query', $query->id) }}"
                                               class="btn btn-primary btn-xs"
                                               title="View full details">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            @if((int) $query->status === 1)
                                                <a href="{{ route('convert.paddy.sell.query', $query->id) }}"
                                                   class="btn btn-success btn-xs"
                                                   title="Convert to paddy trade">
                                                    <i class="fa fa-exchange"></i> Convert to paddy trade
                                                </a>
                                                <a href="{{ route('close.paddy.sell.query', $query->id) }}"
                                                   class="btn btn-danger btn-xs"
                                                   onclick="return confirm('Close this paddy sell query?')">
                                                    Close
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="17" class="text-center">No paddy sell queries found.</td>
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
        $('.paddy-sell-query-datatable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [11, 16] }]
        });
    });
</script>
@endsection
