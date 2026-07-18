@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Live Prices Report
            <small>Report</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Live Prices Report</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border" style="display:flex;justify-content:space-between;align-items:center;">
                <h3 class="box-title">Filter</h3>
                <div>
                    <a class="btn btn-success"
                       href="{{ request()->fullUrlWithQuery(['from' => $from, 'to' => $to, 'crop_year' => $cropYear, 'export' => 'csv', 'page' => null]) }}">
                        <i class="fa fa-download"></i> Export CSV (Full)
                    </a>
                </div>
            </div>
            <div class="box-body">
                <form method="GET" action="{{ request()->url() }}" class="form-inline">
                    <div class="form-group">
                        <label for="from">From</label>
                        <input type="date" id="from" name="from" class="form-control" value="{{ $from ?? '' }}">
                    </div>
                    <div class="form-group" style="margin-left:10px;">
                        <label for="to">To</label>
                        <input type="date" id="to" name="to" class="form-control" value="{{ $to ?? '' }}">
                    </div>
                    <div class="form-group" style="margin-left:10px;">
                        <label for="crop_year">Crop Year</label>
                        <select id="crop_year" name="crop_year" class="form-control">
                            <option value="">All</option>
                            @if(!empty($cropYears))
                                @foreach($cropYears as $y)
                                    <option value="{{ $y }}" {{ (isset($cropYear) && (int)$cropYear === (int)$y) ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-left:10px;">Apply</button>
                </form>
            </div>
        </div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Results</h3>
            </div>
            <div class="box-body table-responsive">
                <table id="live-prices-table" class="table table-bordered table-striped" width="100%">
                    <thead>
                        <tr>
                            <th>Rice Name</th>
                            <th>Rice Form</th>
                            <th>Date</th>
                            <th>Crop Year</th>
                            <th>Min Price</th>
                            <th>Max Price</th>
                            <th>Opening</th>
                            <th>Closing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $r)
                            <tr>
                                <td>{{ $r->rice_name }}</td>
                                <td>{{ $r->rice_form_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($r->created_at)->format('Y-m-d') }}</td>
                                <td>{{ $r->cropYear }}</td>
                                <td>{{ $r->min_price }}</td>
                                <td>{{ $r->max_price }}</td>
                                <td>{{ $r->opening }}</td>
                                <td>{{ $r->closing }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-center">
                    <p>Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }} — Total {{ $rows->total() }}</p>
                    {{ $rows->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
