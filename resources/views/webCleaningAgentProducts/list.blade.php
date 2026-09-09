@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Cleaning Agent Products
            <small>Review &amp; verify</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Vendor Products</li>
            <li class="active">Cleaning Agent</li>
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
                                @if((int) $product->container_20_ft === 1) 20 FT @endif
                                @if((int) $product->container_40_ft === 1) 40 FT @endif
                            </td>
                            <td>
                                {{ $product->port_type ?: '—' }}
                                @if($product->port_location)
                                    <br><small>{{ $product->port_location }}</small>
                                @endif
                            </td>
                            <td>{{ $product->particulars->count() }}</td>
                            <td>
                                @if((int) $product->status === 1)
                                    <span class="label label-success">Verified</span>
                                @else
                                    <span class="label label-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ optional($product->updated_at)->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('get.web.cleaning.agent.products.show', $product->id) }}" class="btn btn-primary btn-xs">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No cleaning agent products yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
