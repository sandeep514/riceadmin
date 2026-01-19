@extends('layouts.main')

@section('content')
<style>
    .nonbasmatitabs .nav>li>a {
        padding: 10px 11px;
    }    
    .basmatitabs .nav>li>a {
        padding: 10px 11px;
    }
</style>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Web Plans
                <small>Plans</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Plans</a></li>
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
                                                            <td style="text-transform: capitalize;">{{ $value->quality ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->brand_year ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->address ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->product_mode ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->logo ?? '' }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->description ?? '' }}</td>

                                                            <td>
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