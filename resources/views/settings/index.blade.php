@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Settings
            <small>Call Now &amp; Market timing (IST)</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Settings</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Timing Settings (Asia/Kolkata)</h3>
                    </div>
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <div class="box-body">
                            <h4 style="margin-top:0;">1. Call Now timing</h4>
                            <p class="help-block">When "Call Now" is available in the app/website.</p>
                            <div class="row">
                                <div class="form-group col-md-6 @error('call_now_from') has-error @enderror">
                                    <label>From (IST)*</label>
                                    <input type="time" name="call_now_from" class="form-control"
                                           value="{{ old('call_now_from', $settings->call_now_from) }}" required>
                                    @error('call_now_from')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6 @error('call_now_to') has-error @enderror">
                                    <label>To (IST)*</label>
                                    <input type="time" name="call_now_to" class="form-control"
                                           value="{{ old('call_now_to', $settings->call_now_to) }}" required>
                                    @error('call_now_to')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <p class="text-muted" style="margin-bottom:20px;">
                                Right now Call Now is
                                <strong class="{{ !empty($callNow['is_available']) ? 'text-success' : 'text-danger' }}">
                                    {{ !empty($callNow['is_available']) ? 'AVAILABLE' : 'UNAVAILABLE' }}
                                </strong>
                                ({{ $callNow['from'] ?? '' }} – {{ $callNow['to'] ?? '' }} IST).
                            </p>

                            <hr>
                            <h4>2. Market open / close timing</h4>
                            <p class="help-block">
                                Always automatic from this window (open inside, closed outside).
                                Admins can still force Open / Closed / Hold from
                                <a href="{{ route('live_prices') }}">Live Prices</a>
                                — that override lasts until the next schedule change, then auto resumes.
                            </p>
                            <div class="row">
                                <div class="form-group col-md-6 @error('market_from') has-error @enderror">
                                    <label>Open From (IST)*</label>
                                    <input type="time" name="market_from" class="form-control"
                                           value="{{ old('market_from', $settings->market_from) }}" required>
                                    @error('market_from')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6 @error('market_to') has-error @enderror">
                                    <label>Open To (IST)*</label>
                                    <input type="time" name="market_to" class="form-control"
                                           value="{{ old('market_to', $settings->market_to) }}" required>
                                    @error('market_to')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <p class="text-muted">
                                Resolved market status now:
                                <strong>{{ strtoupper($resolved['message'] ?? 'open') }}</strong>
                                ({{ $resolved['mode'] ?? 'auto' }}
                                , schedule {{ $resolved['schedule']['from'] ?? '' }}–{{ $resolved['schedule']['to'] ?? '' }} IST
                                @if(!empty($resolved['override_until']))
                                    , override until {{ $resolved['override_until'] }}
                                @endif
                                ).
                            </p>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <a href="{{ route('live_prices') }}" class="btn btn-default">Live Prices (manual Open/Close)</a>
                            <a href="{{ route('rice-price-closings.index') }}" class="btn btn-warning">Upcoming Rice Price Closings</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
