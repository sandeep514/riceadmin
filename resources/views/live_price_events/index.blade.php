@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Live Prices Events
            <small>Master</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('live_prices') }}">Live Prices</a></li>
            <li class="active">Live Prices Events</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Events List</h3>
                        <div class="pull-right">
                            <a href="{{ route('create.live.price.event') }}" class="btn btn-primary btn-sm">Create New Event</a>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Quality Type</th>
                                    <th>Quality</th>
                                    <th>Quality Form</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ \Carbon\Carbon::parse($event->event_date)->format('d-m-Y') }}</td>
                                        <td>{{ optional($event->qualityType)->name ?? '-' }}</td>
                                        <td>{{ optional($event->quality)->name ?? '-' }}</td>
                                        <td>{{ optional($event->qualityForm)->form_name ?? '-' }}</td>
                                        <td>{{ $event->note }}</td>
                                        <td style="white-space: nowrap;">
                                            <a href="{{ route('edit.live.price.event', $event->id) }}" class="btn btn-warning btn-xs">Edit</a>
                                            <form action="{{ route('delete.live.price.event', $event->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this event?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No events found.</td>
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
