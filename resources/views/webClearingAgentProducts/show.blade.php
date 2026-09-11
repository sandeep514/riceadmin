@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Clearing Agent Product
            <small>Review</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Service Providers</li>
            <li><a href="{{ route('get.web.clearing.agent.products.list') }}">Clearing Agent</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-12">
                <a href="{{ route('get.web.clearing.agent.products.list') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                @include('components.vendor-product-status-actions', [
                    'product' => $product,
                    'route' => 'toggle.web.clearing.agent.products.status',
                ])
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Basic details</h3>
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
                            @if((int) $product->status === 1)
                                <span class="label label-success">Verified / Active</span>
                            @else
                                <span class="label label-warning">Pending review</span>
                            @endif
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
                        <th>Container Size</th>
                        <td>
                            @php
                                $sizeLabel = optional($product->containerSizeRel)->label
                                    ?: ($product->container_size ? $product->container_size.' FT' : null);
                            @endphp
                            {{ $sizeLabel ?: '—' }}
                            @if($product->container_size_id)
                                <small class="text-muted">(ID {{ $product->container_size_id }})</small>
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
                        <th>ICD Location</th>
                        <td>
                            {{ optional($product->icdLocationRel)->name ?: ($product->icd_location ?: '—') }}
                            @if($product->icd_location_id)
                                <small class="text-muted">(ID {{ $product->icd_location_id }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Indian Port / Port Location</th>
                        <td>
                            {{ optional($product->indianPortRel)->name ?: ($product->port_location ?: '—') }}
                            @if($product->indian_port_id)
                                <small class="text-muted">(ID {{ $product->indian_port_id }})</small>
                            @endif
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
                        <th>Additional Information</th>
                        <td>{!! nl2br(e($product->additional_information ?: '—')) !!}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Particulars</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Rate</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($product->particulars as $row)
                        <tr>
                            <td>
                                @if((int) $row->is_other === 1)
                                    <span class="label label-default">Other</span>
                                @else
                                    <span class="label label-primary">Master</span>
                                @endif
                            </td>
                            <td>
                                {{ $row->particular_name ?: optional($row->particular)->particular ?: '—' }}
                                @if($row->particular_id)
                                    <small class="text-muted">(ID {{ $row->particular_id }})</small>
                                @endif
                            </td>
                            <td>{{ $row->rate !== null ? '₹ '.$row->rate : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No particulars saved.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
