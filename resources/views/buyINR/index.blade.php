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
                                                <th style="text-align: center ">Grade</th>
                                                <th style="text-align: center ">Packing Type</th>
                                                <th style="text-align: center ">Packing</th>
                                                <th style="text-align: center ">Qualtity</th>
                                                <th style="text-align: center ">Additional Info</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($buyQueries as $k => $v)
                                                <tr>
                                                    <td>{{ ($v->quality_type == 1)? 'Basmati' : 'Non-Basmati'  }}</td>
                                                    <td>{{ $v->RiceQualityMaster->quality }} {{ $v->RiceFormMilestone3->name }}</td>
                                                    <td>{{ $v->riceGrade->getWandType['type'] }} {{ $v->riceGrade->value }}</td>
                                                    <td>{{ App\BuyQueriesINR::$packingTypeStaus[$v->packing_type] }}</td>
                                                    <td>{{ ($v->RicePacking != null) ? $v->RicePacking->packing : '' }}</td>
                                                    <td>{{ $v->quantity }}</td>
                                                    <td>{{ $v->additional_info }}</td>
                                                    <td> {{ App\BuyQueriesINR::$status[$v->status] }}</td>

                                                    <td style="text-align: center;">
                                                        @if( $v->status == 1)
                                                            <a href="{{ route('move.to.trade.buy.queries' , $v->id) }}" class="btn btn-info btn-xs">Moved to trade</a>
                                                            <a href="{{ route('close.buy.queries' , $v->id) }}" class="btn btn-danger btn-xs">Close deal</a>
                                                        @endif
                                                       
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Quality Type</th>
                                                <th style="text-align: center ">Quality</th>
                                                <th style="text-align: center ">Grade</th>
                                                <th style="text-align: center ">Packing Type</th>
                                                <th style="text-align: center ">Packing</th>
                                                <th style="text-align: center ">Qualtity</th>
                                                <th style="text-align: center ">Additional Info</th>
                                                <th style="text-align: center ">Status</th>
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
