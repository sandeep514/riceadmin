@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Forwarder Products
            <small>Review &amp; verify</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Service Providers</li>
            <li class="active">Forwarder Products</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Owner</th>
                        <th>Containers</th>
                        <th>From / Destination</th>
                        <th>Charges</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th width="160">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                {{ optional($product->user)->name ?? ('User #'.$product->user_id) }}
                                @if(!empty(optional($product->user)->email))
                                    <br><small>{{ $product->user->email }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sizeLabels = $product->containerSizes->map(function ($row) {
                                        return optional($row->containerSizeRel)->label
                                            ?: ($row->size ? $row->size.' FT' : null);
                                    })->filter()->values();
                                @endphp
                                {{ $sizeLabels->isNotEmpty() ? $sizeLabels->implode(', ') : '—' }}
                            </td>
                            <td>
                                {{ optional($product->indianPortRel)->name ?: ($product->port_location ?: '—') }}
                                <br><small>
                                    {{ optional($product->destinationPortRel)->name ?: ($product->destination ?: '—') }}
                                </small>
                            </td>
                            <td>{{ $product->charges->count() }}</td>
                            <td>
                                @include('components.vendor-product-status-label', ['product' => $product])
                            </td>
                            <td>{{ optional($product->updated_at)->format('d-m-Y H:i') }}</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('get.web.forwarder.products.show', $product->id) }}" class="btn btn-primary btn-xs">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                @include('components.vendor-product-status-actions', [
                                    'product' => $product,
                                    'route' => 'toggle.web.forwarder.products.status',
                                    'kind' => 'forwarder',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No forwarder products yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
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
