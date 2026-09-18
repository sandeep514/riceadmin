@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Clearing Agent Charges
            <small>Review &amp; verify</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Service Providers</li>
            <li class="active">Clearing Agent Charges</li>
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
                        <th>Container</th>
                        <th>Port</th>
                        <th>Particulars</th>
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
                                    $sizeLabel = optional($product->containerSizeRel)->label
                                        ?: ($product->container_size ? $product->container_size.' FT' : null);
                                @endphp
                                {{ $sizeLabel ?: '—' }}
                            </td>
                            <td>
                                {{ optional($product->portTypeRel)->name ?: ($product->port_type ?: '—') }}
                                @if($product->port_location)
                                    <br><small>{{ $product->port_location }}</small>
                                @endif
                            </td>
                            <td>{{ $product->particulars->count() }}</td>
                            <td>
                                @include('components.vendor-product-status-label', ['product' => $product])
                            </td>
                            <td>{{ optional($product->updated_at)->format('d-m-Y H:i') }}</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('get.web.clearing.agent.products.show', $product->id) }}" class="btn btn-primary btn-xs">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                @include('components.vendor-product-status-actions', [
                                    'product' => $product,
                                    'route' => 'toggle.web.clearing.agent.products.status',
                                    'kind' => 'clearing_agent',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No clearing agent charges yet.</td>
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
