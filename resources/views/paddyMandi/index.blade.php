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
                Paddy Mandi
                <small>Paddy Mandi</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Paddy Mandi</a></li>
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
                                        <a href="{{ route('create.web.paddy.mandi') }}" class="btn btn-sm btn-info">Create</a>
                                        <div class="col-md-12 inputs">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Mandi</th>
                                                    <th>State</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($paddyMandi as $key => $value)
                                                        <tr>
                                                            <td style="text-transform: capitalize;">{{ $value->mandi }}</td>
                                                            <td style="text-transform: capitalize;">{{ $value->state_rel->state }}</td>
                                                            <td>
                                                                <a class="btn btn-sm btn-info" href="{{ route('edit.web.paddy.mandi' , $value->id ) }}"> Edit </a>
                                                                <a class="btn btn-sm btn-{{ ($value->status)?'danger' : 'info' }}" href="{{ route('update.status.web.paddy.mandi' , $value->id ) }}"> {{ ($value->status)?'De-Active' : 'Active' }} </a>
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