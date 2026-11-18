@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Settings
            <small>Call Now, Live, Rice sourcing &amp; Paddy timing (IST)</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Settings</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-8">

                {{-- 1. Call Now --}}
                <div class="box box-primary" id="call-now">
                    <div class="box-header with-border">
                        <h3 class="box-title">1. Call Now timing</h3>
                    </div>
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="call_now">
                        <div class="box-body">
                            <p class="help-block">When "Call Now" is available in the app/website. Times are Asia/Kolkata.</p>
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
                            <p class="text-muted" style="margin-bottom:0;">
                                Right now Call Now is
                                <strong class="{{ !empty($callNow['is_available']) ? 'text-success' : 'text-danger' }}">
                                    {{ !empty($callNow['is_available']) ? 'AVAILABLE' : 'UNAVAILABLE' }}
                                </strong>
                                ({{ $callNow['from'] ?? '' }} – {{ $callNow['to'] ?? '' }} IST).
                            </p>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>

                {{-- 2. Live market --}}
                <div class="box box-primary" id="live-market">
                    <div class="box-header with-border">
                        <h3 class="box-title">2. Market open / close timing</h3>
                    </div>
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="live_market">
                        <div class="box-body">
                            <p class="help-block">
                                Automatic from this window (open inside, closed outside).
                                Admins can still force Open / Closed / Hold from
                                <a href="{{ route('live_prices') }}">Live Prices</a>
                                — that override lasts until the next schedule change, then auto resumes.
                                Use <strong>Upcoming Closings</strong> for festival/strike closed days.
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
                            <p class="text-muted" style="margin-bottom:0;">
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
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('rice-price-closings.index', ['market' => 'live']) }}"
                               class="btn btn-warning">Upcoming Closings</a>
                        </div>
                    </form>
                </div>

                {{-- 3. Rice sourcing --}}
                <div class="box box-primary" id="rice-sourcing">
                    <div class="box-header with-border">
                        <h3 class="box-title">3. Rice sourcing open / close timing</h3>
                    </div>
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="rice_sourcing">
                        <div class="box-body">
                            <p class="help-block">
                                Automatic open/close for rice sourcing trades.
                                Admins can still force Open / Closed / Hold from
                                <a href="{{ route('master.trade') }}">Rice Sourcing</a>
                                — that override lasts until the next schedule change, then auto resumes.
                                Use <strong>Upcoming Closings</strong> for festival/strike closed days.
                            </p>
                            <div class="row">
                                <div class="form-group col-md-6 @error('rice_sourcing_from') has-error @enderror">
                                    <label>Open From (IST)*</label>
                                    <input type="time" name="rice_sourcing_from" class="form-control"
                                           value="{{ old('rice_sourcing_from', $settings->rice_sourcing_from ?? '09:00') }}" required>
                                    @error('rice_sourcing_from')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6 @error('rice_sourcing_to') has-error @enderror">
                                    <label>Open To (IST)*</label>
                                    <input type="time" name="rice_sourcing_to" class="form-control"
                                           value="{{ old('rice_sourcing_to', $settings->rice_sourcing_to ?? '18:00') }}" required>
                                    @error('rice_sourcing_to')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <p class="text-muted" style="margin-bottom:0;">
                                Resolved rice sourcing status now:
                                <strong>{{ strtoupper($resolvedRice['message'] ?? 'open') }}</strong>
                                ({{ $resolvedRice['mode'] ?? 'auto' }}
                                , schedule {{ $resolvedRice['schedule']['from'] ?? '' }}–{{ $resolvedRice['schedule']['to'] ?? '' }} IST
                                @if(!empty($resolvedRice['override_until']))
                                    , override until {{ $resolvedRice['override_until'] }}
                                @endif
                                ).
                            </p>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('rice-price-closings.index', ['market' => 'rice_sourcing']) }}"
                               class="btn btn-warning">Upcoming Closings</a>
                        </div>
                    </form>
                </div>

                {{-- 4. Paddy --}}
                <div class="box box-primary" id="paddy-market">
                    <div class="box-header with-border">
                        <h3 class="box-title">4. Paddy pricing open / close timing</h3>
                    </div>
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="paddy_market">
                        <div class="box-body">
                            <p class="help-block">
                                Automatic open/close for paddy pricing / paddy trades.
                                Admins can still force Open / Closed / Hold from
                                <a href="{{ route('list.paddy.trades') }}">Paddy Trades</a>
                                — that override lasts until the next schedule change, then auto resumes.
                                Use <strong>Upcoming Closings</strong> for festival/strike closed days.
                            </p>
                            <div class="row">
                                <div class="form-group col-md-6 @error('paddy_market_from') has-error @enderror">
                                    <label>Open From (IST)*</label>
                                    <input type="time" name="paddy_market_from" class="form-control"
                                           value="{{ old('paddy_market_from', $settings->paddy_market_from ?? '09:00') }}" required>
                                    @error('paddy_market_from')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6 @error('paddy_market_to') has-error @enderror">
                                    <label>Open To (IST)*</label>
                                    <input type="time" name="paddy_market_to" class="form-control"
                                           value="{{ old('paddy_market_to', $settings->paddy_market_to ?? '18:00') }}" required>
                                    @error('paddy_market_to')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <p class="text-muted" style="margin-bottom:0;">
                                Resolved paddy market status now:
                                <strong>{{ strtoupper($resolvedPaddy['message'] ?? 'open') }}</strong>
                                ({{ $resolvedPaddy['mode'] ?? 'auto' }}
                                , schedule {{ $resolvedPaddy['schedule']['from'] ?? '' }}–{{ $resolvedPaddy['schedule']['to'] ?? '' }} IST
                                @if(!empty($resolvedPaddy['override_until']))
                                    , override until {{ $resolvedPaddy['override_until'] }}
                                @endif
                                ).
                            </p>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('rice-price-closings.index', ['market' => 'paddy']) }}"
                               class="btn btn-warning">Upcoming Closings</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection
