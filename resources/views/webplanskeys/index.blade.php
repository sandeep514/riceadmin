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
                Web Plans keys
                <small>Plans keys</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Plans keys</a></li>
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
                                        <a href="{{ route('list.web.plans.keys.create') }}" class="btn btn-sm btn-info">Create</a>
                                        <div class="col-md-12 inputs">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Key</th>
                                                    <th>Status</th>
                                                    <th style="white-space: nowrap;">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($webPlanKeys as $key => $value)
                                                        @php $isActive = (int)($value->status ?? 0) === 1; @endphp
                                                        <tr>
                                                            <td style="text-transform: capitalize;">{{ $value->key }}</td>
                                                            <td>
                                                                @if($isActive)
                                                                    <span class="label label-success">Active</span>
                                                                @else
                                                                    <span class="label label-default">Inactive</span>
                                                                @endif
                                                            </td>
                                                            <td style="white-space: nowrap;">
                                                                <a class="btn btn-sm btn-info" href="{{ route('web.plans.keys.edit' , $value->id ) }}"> Edit </a>
                                                                @if($isActive)
                                                                    <a class="btn btn-sm btn-danger" href="{{ route('web.plans.keys.status.update' , $value->id ) }}"> De-active </a>
                                                                @else
                                                                    <a class="btn btn-sm btn-success" href="{{ route('web.plans.keys.status.update' , $value->id ) }}"> Active </a>
                                                                @endif
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