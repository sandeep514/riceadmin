@extends('layouts.main')

@section('content')
@php
    $market = $market ?? \App\RicePriceClosing::MARKET_LIVE;
    $marketLabel = $marketLabel ?? \App\RicePriceClosing::marketLabel($market);
@endphp
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Upcoming {{ $marketLabel }} Closings
            <small>Special market closed days (festivals, strikes, etc.)</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('settings.index') }}">Settings</a></li>
            <li class="active">Upcoming Closings</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom:15px;">
            <div class="col-md-12">
                <div class="btn-group">
                    <a href="{{ route('rice-price-closings.index', ['market' => 'live', 'month' => $month]) }}"
                       class="btn btn-sm {{ $market === 'live' ? 'btn-primary' : 'btn-default' }}">Live prices</a>
                    <a href="{{ route('rice-price-closings.index', ['market' => 'rice_sourcing', 'month' => $month]) }}"
                       class="btn btn-sm {{ $market === 'rice_sourcing' ? 'btn-primary' : 'btn-default' }}">Rice sourcing</a>
                    <a href="{{ route('rice-price-closings.index', ['market' => 'paddy', 'month' => $month]) }}"
                       class="btn btn-sm {{ $market === 'paddy' ? 'btn-primary' : 'btn-default' }}">Paddy pricing</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add closing day — {{ $marketLabel }}</h3>
                    </div>
                    <form method="POST" action="{{ route('rice-price-closings.store') }}">
                        @csrf
                        <input type="hidden" name="market" value="{{ $market }}">
                        <div class="box-body">
                            <div class="form-group @error('close_date') has-error @enderror">
                                <label>Closing date (IST)*</label>
                                <input type="date" name="close_date" class="form-control"
                                       value="{{ old('close_date') }}" required>
                                @error('close_date')<span class="help-block text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group @error('reason') has-error @enderror">
                                <label>Reason*</label>
                                <textarea name="reason" class="form-control" rows="3"
                                          placeholder="e.g. Diwali holiday / Mill strike" required>{{ old('reason') }}</textarea>
                                @error('reason')<span class="help-block text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Add Closing</button>
                            <a href="{{ route('settings.index') }}" class="btn btn-default">Settings</a>
                        </div>
                    </form>
                </div>

                @if($upcoming->isNotEmpty())
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Next upcoming</h3>
                    </div>
                    <div class="box-body" style="padding:0;">
                        <ul class="list-group" style="margin:0;">
                            @foreach($upcoming as $item)
                                <li class="list-group-item">
                                    <strong>{{ $item->close_date->format('d M Y') }}</strong>
                                    <span class="text-muted"> — {{ $item->reason }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-7">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Closings for {{ $monthStart->format('F Y') }}</h3>
                        <form method="GET" action="{{ route('rice-price-closings.index') }}" class="form-inline pull-right" style="margin:0;">
                            <input type="hidden" name="market" value="{{ $market }}">
                            <input type="month" name="month" class="form-control input-sm"
                                   value="{{ $month }}" onchange="this.form.submit()">
                        </form>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width:140px;">Date</th>
                                    <th>Reason</th>
                                    <th style="width:160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($closings as $closing)
                                    <tr>
                                        <td>{{ $closing->close_date->format('d M Y') }}</td>
                                        <td>
                                            <form id="edit-closing-{{ $closing->id }}" method="POST"
                                                  action="{{ route('rice-price-closings.update', $closing->id) }}"
                                                  style="margin:0;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="close_date"
                                                       value="{{ $closing->close_date->format('Y-m-d') }}">
                                                <input type="text" name="reason" class="form-control input-sm"
                                                       value="{{ $closing->reason }}" required>
                                            </form>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <button type="submit" form="edit-closing-{{ $closing->id }}"
                                                    class="btn btn-primary btn-xs">Save</button>
                                            <form method="POST"
                                                  action="{{ route('rice-price-closings.destroy', $closing->id) }}"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Delete this closing day?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            No closings for this month.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
