@extends('layouts.main')

@section('content')
@php
    $liveStatus = (int) ($resolved['currentStatus'] ?? 1);
    $riceStatus = (int) ($resolvedRice['currentStatus'] ?? 1);
    $paddyStatus = (int) ($resolvedPaddy['currentStatus'] ?? 1);
@endphp
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
                                Use Open / Closed / Hold below for a temporary override until the next schedule change.
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
                            <span style="margin-left:8px;">
                                <a href="{{ route('update.live.price.market.status', 'open') }}"
                                   class="btn btn-success btn-sm {{ $liveStatus === 1 ? 'active' : '' }}">Open</a>
                                <a href="{{ route('update.live.price.market.status', 'closed') }}"
                                   class="btn btn-danger btn-sm {{ $liveStatus === 2 ? 'active' : '' }}">Closed</a>
                                <a href="{{ route('update.live.price.market.status', 'hold') }}"
                                   class="btn btn-warning btn-sm {{ $liveStatus === 3 ? 'active' : '' }}">Hold</a>
                            </span>
                            <a href="{{ route('rice-price-closings.index') }}" class="btn btn-default btn-sm" style="margin-left:8px;">Upcoming Closings</a>
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
                                Use Open / Closed / Hold below for a temporary override until the next schedule change.
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
                            <span style="margin-left:8px;">
                                <a href="{{ route('master.update.trade.create', ['tradeStatus' => 1]) }}"
                                   class="btn btn-success btn-sm {{ $riceStatus === 1 ? 'active' : '' }}">Open</a>
                                <a href="{{ route('master.update.trade.create', ['tradeStatus' => 11]) }}"
                                   class="btn btn-danger btn-sm {{ $riceStatus === 11 ? 'active' : '' }}">Closed</a>
                                <a href="{{ route('master.update.trade.create', ['tradeStatus' => 12]) }}"
                                   class="btn btn-warning btn-sm {{ $riceStatus === 12 ? 'active' : '' }}">Hold</a>
                            </span>
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
                                Use Open / Closed / Hold below for a temporary override until the next schedule change.
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
                            <span style="margin-left:8px;">
                                <a href="{{ route('update.paddy.market.status', ['tradeStatus' => 1]) }}"
                                   class="btn btn-success btn-sm {{ $paddyStatus === 1 ? 'active' : '' }}">Open</a>
                                <a href="{{ route('update.paddy.market.status', ['tradeStatus' => 11]) }}"
                                   class="btn btn-danger btn-sm {{ $paddyStatus === 11 ? 'active' : '' }}">Closed</a>
                                <a href="{{ route('update.paddy.market.status', ['tradeStatus' => 12]) }}"
                                   class="btn btn-warning btn-sm {{ $paddyStatus === 12 ? 'active' : '' }}">Hold</a>
                            </span>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection
