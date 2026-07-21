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
                Paddy States
                <small>Paddy State</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Paddy State</a></li>
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
                                        <a href="{{ route('create.web.paddy.state') }}" class="btn btn-sm btn-info">Create</a>
                                        <div class="col-md-12 inputs">
                                            <table class="table table-striped table-bordered paddy-datatable" width="100%">
                                                <thead>
                                                <tr>
                                                    <th>State</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($paddyState as $key => $value)
                                                        <tr>
                                                            <td style="text-transform: capitalize;">{{ $value->state }}</td>
                                                            <td>
                                                                <a class="btn btn-sm btn-info" href="{{ route('edit.web.paddy.state' , $value->id ) }}"> Edit </a>
                                                                <a class="btn btn-sm btn-{{($value->status == 1)? 'danger' : 'info'}}" href="{{ route('update.status.web.paddy.state' , $value->id ) }}"> {{($value->status == 1)? 'De-active' : 'Active'}} </a>
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

@section('javascript')
<script>
    $(function () {
        $('.paddy-datatable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [1] }]
        });
    });
</script>
@endsection