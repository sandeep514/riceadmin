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
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Key</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($webPlanKeys as $key => $value)
                                                        <tr>
                                                            <td style="text-transform: capitalize;">{{ $value->title??'--' }}</td>
                                                            <td>
                                                                <a class="btn btn-sm btn-info" href="{{ route('web.plans.edit' , $value->id ) }}"> Edit </a>
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