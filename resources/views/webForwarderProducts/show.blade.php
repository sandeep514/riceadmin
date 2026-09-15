@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Forwarder Product
            <small>Review</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Service Providers</li>
            <li><a href="{{ route('get.web.forwarder.products.list') }}">Forwarder</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-12">
                <a href="{{ route('get.web.forwarder.products.list') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                @include('components.vendor-product-status-actions', [
                                            'product' => $product,
                                            'route' => 'toggle.web.forwarder.products.status',
                                            'kind' => 'forwarder',
                                        ])
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Basic details</h3>
                @if($product->updated_at)
                    <span class="pull-right text-muted">
                        Last Updated on {{ $product->updated_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d F Y') }}
                    </span>
                @endif
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">ID</th>
                        <td>{{ $product->id }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @include('components.vendor-product-status-label', ['product' => $product])
                        </td>
                    </tr>
                    <tr>
                        <th>Owner</th>
                        <td>
                            {{ optional($product->user)->name ?? ('User #'.$product->user_id) }}
                            @if(!empty(optional($product->user)->email))
                                <br><small>{{ $product->user->email }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Port Type</th>
                        <td>
                            {{ optional($product->portTypeRel)->name ?: ($product->port_type ?: '—') }}
                            @if($product->port_type_id)
                                <small class="text-muted">(ID {{ $product->port_type_id }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>ICD</th>
                        <td>
                            {{ optional($product->icdLocationRel)->name ?: ($product->icd_location ?: '—') }}
                            @if($product->icd_location_id)
                                <small class="text-muted">(ID {{ $product->icd_location_id }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Indian Port</th>
                        <td>
                            {{ optional($product->indianPortRel)->name ?: ($product->port_location ?: '—') }}
                            @if($product->indian_port_id)
                                <small class="text-muted">(ID {{ $product->indian_port_id }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Region</th>
                        <td>
                            {{ optional($product->regionRel)->name
                                ?: (optional(optional($product->destinationPortRel)->region)->name ?: '—') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Country</th>
                        <td>
                            {{ optional($product->countryRel)->name
                                ?: (optional(optional($product->destinationPortRel)->country)->name ?: '—') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Destination Port</th>
                        <td>
                            {{ optional($product->destinationPortRel)->name ?: ($product->destination ?: '—') }}
                            @if($product->destination_port_id)
                                <small class="text-muted">(ID {{ $product->destination_port_id }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Container Type (Dry)</th>
                        <td>
                            @forelse($product->containerSizes as $row)
                                @php
                                    $label = optional($row->containerSizeRel)->label
                                        ?: ($row->size ? $row->size.' FT' : null);
                                @endphp
                                <span class="label label-success">{{ $label ?: '—' }}</span>
                            @empty
                                —
                            @endforelse
                        </td>
                    </tr>
                    <tr>
                        <th>Additional Information</th>
                        <td>{!! nl2br(e($product->additional_information ?: '—')) !!}</td>
                    </tr>
                </table>
            </div>
        </div>

        @php
            $totalUsd = 0;
            $totalInr = 0;
            foreach ($product->charges as $row) {
                if (strtoupper((string) $row->currency) === 'USD' && is_numeric($row->charges)) {
                    $totalUsd += (float) $row->charges;
                }
                if (is_numeric($row->inr_amount)) {
                    $totalInr += (float) $row->inr_amount;
                } elseif (strtoupper((string) $row->currency) === 'INR' && is_numeric($row->charges)) {
                    $totalInr += (float) $row->charges;
                }
            }
        @endphp

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Charges</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Currency</th>
                        <th>Charges</th>
                        <th>Exc</th>
                        <th>INR</th>
                        <th>Remarks</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($product->charges as $row)
                        <tr>
                            <td>
                                {{ $row->title ?: optional($row->titleRel)->name ?: '—' }}
                                @if((int) $row->is_other === 1)
                                    <span class="label label-default">Other</span>
                                @endif
                            </td>
                            <td>{{ strtoupper($row->currency ?: 'INR') }}</td>
                            <td>{{ $row->charges !== null && $row->charges !== '' ? $row->charges : '—' }}</td>
                            <td>{{ $row->exchange_rate !== null && $row->exchange_rate !== '' ? $row->exchange_rate : '—' }}</td>
                            <td>{{ $row->inr_amount !== null && $row->inr_amount !== '' ? $row->inr_amount : '—' }}</td>
                            <td>{{ $row->remarks ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No charges saved.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if($product->charges->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Totals</th>
                            <th>
                                <span class="label label-success">USD {{ number_format($totalUsd, 0) }}</span>
                            </th>
                            <th></th>
                            <th>
                                <span class="label label-success">INR {{ number_format($totalInr, 0) }}</span>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@section('javascript')
@include('components.vendor-product-deactivate-modal')
@include('components.vendor-product-ask-vendor-modal')
@endsection
