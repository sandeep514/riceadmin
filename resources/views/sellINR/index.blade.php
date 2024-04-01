@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Sell Query
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> sell</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Sell Query INR</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <div class="col-md-12">
                                    <table id="example2" class="display" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <!-- <th style="text-align: center ">sno</th> -->
                                                <th style="text-align: center ">Quality Type</th>
                                                <th style="text-align: center ">Quality</th>
                                                <th style="text-align: center ">QualityForm</th>
                                                <th style="text-align: center ">Grade</th>
                                                <th style="text-align: center ">Packing</th>
                                                <th style="text-align: center ">Quantity</th>
                                                <th style="text-align: center ">Ex-factory Price</th>
                                                <th style="text-align: center ">ValidDays</th>
                                                <th style="text-align: center ">Packing File</th>
                                                <th style="text-align: center ">Uncooked File</th>
                                                <th style="text-align: center ">Cooked File</th>
                                                <th style="text-align: center ">Warehouse Location</th>
                                                <th style="text-align: center ">Contact Person</th>
                                                <th style="text-align: center ">Contact Phone</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">Created at</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($sellerQueries as $k => $v)

                                                <tr>
                                                    <!-- <td>{{ $v->id }}</td> -->
                                                    <td>{{ ($v->RiceQualityRiceNames->type)??'---'  }}</td>
                                                    <td>{{ ($v->RiceQualityRiceNames->name)??'---' }}</td>
                                                    <td>{{ $v->RiceFormMilestone3->name }}</td>
                                                    <td>{{ ($v->riceGrade->getWandType['type'])??'--' }} {{ ($v->riceGrade->value)??'--' }}</td>
                                                    <td>{{ ($v->RicePacking->packing)?? '--' }}</td>
                                                    <td>{{ $v->quantity }}</td>
                                                    <td>{{ $v->offerPrice }}</td>
                                                    <td>{{ $v->validDays }}</td>
                                                    <td><div style="width: 100px;height: 100px"><a href="{{ asset('uploads/'.$v->packing_file) }}" download><img src="{{ asset('uploads/'.$v->packing_file) }}" style="width: 70px" /></a></div></td>
                                                    <td><div style="width: 100px;height: 100px"><a href="{{ asset('uploads/'.$v->uncooked_file) }}" download><img src="{{ asset('uploads/'.$v->uncooked_file) }}" style="width: 70px" /></a></div></td>
                                                    <td><div style="width: 100px;height: 100px"><a href="{{ asset('uploads/'.$v->cooked_file) }}" download><img src="{{ asset('uploads/'.$v->cooked_file) }}" style="width: 70px" /></a></div></td>
                                                    <td>{{ $v->warehouselocation }}</td>
                                                    <td>{{ $v->contactperson }}</td>
                                                    <td>{{ $v->contactMobile }}</td>
                                                    <td>{{ App\SellQueriesINR::$status[$v->status] }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($v->created_at)->format('d-m-Y') }}</td>

                                                    <td style="text-align: center;">
                                                        @if( $v->status == 1)
                                                            <a href="{{ route('move.to.trade.sell.queries' , $v->id) }}" class="btn btn-info btn-xs">Moved to trade</a>
                                                            <a href="{{ route('close.sell.queries' , $v->id) }}" class="btn btn-danger btn-xs">Close deal</a>
                                                        @endif
                                                       
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Quality Type</th>
                                                <th style="text-align: center ">Quality</th>
                                                <th style="text-align: center ">QualityForm</th>
                                                <th style="text-align: center ">Grade</th>
                                                <th style="text-align: center ">Packing</th>
                                                <th style="text-align: center ">Quantity</th>
                                                <th style="text-align: center ">Ex-factory Price</th>
                                                <th style="text-align: center ">ValidDays</th>
                                                <th style="text-align: center ">Packing File</th>
                                                <th style="text-align: center ">Uncooked File</th>
                                                <th style="text-align: center ">Cooked File</th>
                                                <th style="text-align: center ">Warehouse Location</th>
                                                <th style="text-align: center ">Contact Person</th>
                                                <th style="text-align: center ">Contact Phone</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">Created at</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
@endsection
