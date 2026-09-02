@extends('layouts.main')

@section('content')
<style>
    .vendor-product-thumb {
        max-width: 56px;
        max-height: 56px;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        padding: 2px;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Lab Equipment Products
            <small>Review &amp; verify</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Vendor Products</li>
            <li><a href="{{ route('get.web.lab.equipment.products.list') }}">Lab Equipment Products</a></li>
            <li class="active">List</li>
        </ol>
    </section>

    <section class="content">
        <div class="box-body">
            <div class="row text-left" style="margin-top: 20px;">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered" id="labEquipmentProductsTable" width="100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Owner</th>
                            <th>Equipments</th>
                            <th>Variants</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                @php
                                    $firstCatalogue = optional($product->variants->first())->catalogue;
                                    $catalogueUrl = $firstCatalogue
                                        ? asset('uploads/lab-equipment-products/'.$product->user_id.'/'.$firstCatalogue)
                                        : null;
                                @endphp
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>
                                        {{ optional($product->user)->name ?? '—' }}
                                        @if(optional($product->user)->email)
                                            <br><small>{{ $product->user->email }}</small>
                                        @endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($product->variants->pluck('equipment_name')->filter()->implode(', ') ?: '—', 70) }}</td>
                                    <td style="white-space:nowrap;">
                                        @if($catalogueUrl)
                                            <img src="{{ $catalogueUrl }}" alt="" class="vendor-product-thumb" onerror="this.style.display='none';">
                                        @endif
                                        {{ $product->variants->count() }} variant(s)
                                    </td>
                                    <td>
                                        @if((int) $product->status === 1)
                                            <span class="label label-success">Verified / Active</span>
                                        @else
                                            <span class="label label-warning">Pending review</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->created_at ? \Carbon\Carbon::parse($product->created_at)->format('d-m-Y H:i') : '—' }}</td>
                                    <td style="white-space:nowrap;">
                                        <a class="btn btn-sm btn-primary" href="{{ route('get.web.lab.equipment.products.show', $product->id) }}">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        @include('components.vendor-product-status-actions', [
                                            'product' => $product,
                                            'route' => 'toggle.web.lab.equipment.products.status',
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('javascript')
@include('components.vendor-product-deactivate-modal')
<script>
    $(function () {
        $('#labEquipmentProductsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [6] }]
        });
    });
</script>
@endsection
