@extends('layouts.main')

@section('content')
<style>
    .web-brand-logo-lg {
        max-width: 180px;
        max-height: 180px;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        padding: 6px;
    }
    .web-brand-variant-img {
        max-width: 80px;
        max-height: 80px;
        object-fit: contain;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #fff;
        padding: 2px;
        margin-right: 4px;
    }
    .web-brand-detail-table th {
        width: 180px;
        background: #f9f9f9;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Web Brand
            <small>View</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('get.web.brands.list') }}">Web Brands</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12" style="margin-bottom:10px;">
                <a href="{{ route('get.web.brands.list') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                <a class="btn btn-sm btn-{{((int)$brand->status === 0) ? 'info' : 'danger'}}"
                   href="{{ route('toggle.web.brands.status', $brand->id) }}">
                    {{ ((int)$brand->status === 0) ? 'Active' : 'De-Active' }}
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Brand details</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered web-brand-detail-table">
                            <tr>
                                <th>ID</th>
                                <td>{{ $brand->id }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td style="text-transform:capitalize;">{{ $brand->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Quality</th>
                                <td>{{ $brand->RiceName->name ?? ($brand->quality ?? '—') }}</td>
                            </tr>
                            <tr>
                                <th>Brand year</th>
                                <td>{{ $brand->brand_year ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $brand->address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Product mode</th>
                                <td>{{ $brand->product_mode ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $brand->description ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if((int)$brand->status === 1)
                                        <span class="label label-success">Active</span>
                                    @else
                                        <span class="label label-warning">Pending / De-Active</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Owner user</th>
                                <td>
                                    @if($brand->userRel)
                                        {{ $brand->userRel->name ?? '—' }}
                                        (ID: {{ $brand->user_id }})
                                        @if(!empty($brand->userRel->email))
                                            <br><small>{{ $brand->userRel->email }}</small>
                                        @endif
                                        @if(!empty($brand->userRel->mobile))
                                            <br><small>{{ $brand->userRel->mobile }}</small>
                                        @endif
                                    @else
                                        {{ $brand->user_id ? 'User #'.$brand->user_id : '—' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created</th>
                                <td>{{ $brand->created_at ? \Carbon\Carbon::parse($brand->created_at)->format('d-m-Y H:i') : '—' }}</td>
                            </tr>
                            <tr>
                                <th>Updated</th>
                                <td>{{ $brand->updated_at ? \Carbon\Carbon::parse($brand->updated_at)->format('d-m-Y H:i') : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">Logo</h3>
                    </div>
                    <div class="box-body text-center">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $brand->name }}" class="web-brand-logo-lg"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <p class="text-muted" style="display:none;">Logo file missing</p>
                            <p class="help-block" style="margin-top:10px;word-break:break-all;font-size:11px;">{{ $brand->logo }}</p>
                        @else
                            <p class="text-muted">No logo uploaded</p>
                        @endif
                    </div>
                </div>

                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">Availability</h3>
                    </div>
                    <div class="box-body">
                        @forelse($availability as $state)
                            <div style="margin-bottom:10px;">
                                <strong>{{ $state['state_name'] ?? ('State #'.$state['state_id']) }}</strong>
                                @if(!empty($state['cities']))
                                    <ul style="margin:4px 0 0 16px;padding:0;">
                                        @foreach($state['cities'] as $city)
                                            <li>{{ $city['city_name'] ?? ('City #'.$city['city_id']) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-muted" style="font-size:12px;">No cities listed</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">No availability mapped</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Variants ({{ $brand->getVariants->count() }})</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Variant</th>
                                    <th>Quality</th>
                                    <th>Form</th>
                                    <th>Grade</th>
                                    <th>Packing</th>
                                    <th>Image</th>
                                    <th>Cut image</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($brand->getVariants as $variant)
                                    <tr>
                                        <td>{{ $variant->id }}</td>
                                        <td>{{ $variant->variant ?? '—' }}</td>
                                        <td>{{ optional($variant->qualityRel)->name ?? ($variant->quality_id ?? '—') }}</td>
                                        <td>{{ optional($variant->formRel)->form_name ?? ($variant->form_id ?? '—') }}</td>
                                        <td>{{ $variant->grade ?? '—' }}</td>
                                        <td>{{ $variant->packing ?? '—' }}</td>
                                        <td>
                                            @if(!empty($variant->image))
                                                <img src="{{ $variantImageBase }}/{{ $variant->image }}"
                                                     alt="variant"
                                                     class="web-brand-variant-img"
                                                     onerror="this.style.display='none';">
                                                <div style="font-size:10px;color:#999;word-break:break-all;">{{ $variant->image }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($variant->cut_image))
                                                <img src="{{ $variantImageBase }}/{{ $variant->cut_image }}"
                                                     alt="cut"
                                                     class="web-brand-variant-img"
                                                     onerror="this.style.display='none';">
                                                <div style="font-size:10px;color:#999;word-break:break-all;">{{ $variant->cut_image }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if((int)($variant->status ?? 1) === 1)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-default">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No variants for this brand</td>
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
