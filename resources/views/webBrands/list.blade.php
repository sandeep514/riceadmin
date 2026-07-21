@extends('layouts.main')

@section('content')
<style>
    .nonbasmatitabs .nav>li>a {
        padding: 10px 11px;
    }
    .basmatitabs .nav>li>a {
        padding: 10px 11px;
    }
    .web-brand-logo-thumb {
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
                Web Brands
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('get.web.brands.list') }}">Web Brands</a></li>
                <li class="active">List</li>
            </ol>
        </section>

        <section class="content">
            <div class="box-body">

                <div class="responsiveTabs basmatitabs">
                    <div id="myTabContent" class="tab-content" >
                        <div class="">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <div class="row text-left" style="margin-top: 20px;">
                                        <a href="{{ route('list.web.plans.create') }}" class="btn btn-sm btn-info">Create</a>
                                        <div class="col-md-12 inputs">
                                            <table class="table table-striped example2" id="example2">
                                                <thead>
                                                <tr>
                                                    <td>Name</td>
                                                    <td>Quality</td>
                                                    <td>Brand year</td>
                                                    <td>Address</td>
                                                    <td>Product mode</td>
                                                    <td>Logo</td>
                                                    <td>Description</td>
                                                    <td>Action</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($brands as $key => $value)
                                                        <tr>
                                                            <td style="text-transform: capitalize;">{{ $value->name ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->RiceName->name ?? $value->quality ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->brand_year ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->address ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->product_mode ?? '' }}</td>
                                                            <td>
                                                                @if(!empty($value->logo))
                                                                    <img src="{{ asset('brands/'.$value->logo) }}"
                                                                         alt="{{ $value->name }}"
                                                                         class="web-brand-logo-thumb"
                                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                                                    <span style="display:none;font-size:11px;color:#999;">No image</span>
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                            <td style="text-transform: capitalize;">{{ \Illuminate\Support\Str::limit($value->description ?? '', 80) }}</td>

                                                            <td style="white-space:nowrap;">
                                                                <a class="btn btn-sm btn-primary" href="{{ route('get.web.brands.show', $value->id) }}">
                                                                    <i class="fa fa-eye"></i> View
                                                                </a>
                                                                <a class="btn btn-sm btn-{{($value->status == 0) ? 'info' : 'danger'}}" href="{{ route('toggle.web.brands.status' , $value->id ) }}"> {{ ($value->status == 0)? 'Active' : 'De-Active' }} </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/live-price.js?ref='.rand(1111,9999)) }}"></script>
@endsection
