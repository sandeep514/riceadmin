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
                List Cities
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Cities</a></li>
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
                                        <div class="col-md-12 inputs">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Actions</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($cities as $key => $value)
                                                        <tr>
                                                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</td>
                                                            <td>
                                                                <ul style="list-style: none;display: inline-flex;padding: 0">
                                                                    {{-- <li>
                                                                        <a class="btn btn-sm btn-{{ ( $value == 1 ) ? 'primary' : 'info' }}" href="{{ route('master.city.changeStatus' , base64_encode($key)) }}"> {{ ($value == 1) ? 'Disable' : 'Enable' }} </a>
                                                                    </li> --}}

                                                                    <li style="margin-left: 20px">
                                                                        <a class="btn btn-sm btn-info" href="{{ route('master.get.city' , base64_encode($key)) }}"> Edit </a>
                                                                    </li>
                                                                    <li style="margin-left: 20px">
                                                                        <a class="btn btn-sm btn-danger" href="{{ route('master.delete.city' , base64_encode($key)) }}"> Delete </a>
                                                                    </li>
                                                                </ul>
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