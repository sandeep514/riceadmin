@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Live Prices Events
            <small>Create</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('live_prices') }}">Live Prices</a></li>
            <li><a href="{{ route('live.price.events') }}">Live Prices Events</a></li>
            <li class="active">Create</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Create Event</h3>
                    </div>
                    <form action="{{ route('save.live.price.event') }}" method="POST">
                        @csrf
                        <div class="box-body">
                            @include('live_price_events._form')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
