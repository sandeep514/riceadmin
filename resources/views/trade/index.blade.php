@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Trade
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Trade</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Trade</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-11">
                            
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('master.trade.create') }}" class="btn btn-info btn-sm">Create</a>
                            </div>
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
                                                <th style="text-align: center ">OfferPrice</th>
                                                <th style="text-align: center ">ValidDays</th>
                                                <th style="text-align: center ">Packing File</th>
                                                <th style="text-align: center ">Uncooked File</th>
                                                <th style="text-align: center ">Cooked File</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($sellQueries as $k => $v)
                                                <tr>
                                                    <!-- <td>{{ $v->id }}</td> -->
                                                    <td>{{ ($v->quality_type == 1)? 'Basmati' : 'Non-Basmati'  }}</td>
                                                    <td>{{ $v->RiceQualityMaster->quality }}</td>
                                                    <td>{{ $v->RiceFormMilestone3->name }}</td>
                                                    <td>{{ $v->riceGrade->getWandType[0]['type'] }} {{ $v->riceGrade->value }}</td>
                                                    <td>{{ $v->RicePacking->packing }}</td>
                                                    <td>{{ $v->quantity }}</td>
                                                    <td>{{ $v->offerPrice }}</td>
                                                    <td>{{ $v->validDays }}</td>
                                                    <td><div style="width: 100px;height: 100px"><img src="{{ asset('uploads/'.$v->packing_file) }}" style="width: 70px" /></div></td>
                                                    <td><div style="width: 100px;height: 100px"><img src="{{ asset('uploads/'.$v->uncooked_file) }}" style="width: 70px" /></div></td>
                                                    <td><div style="width: 100px;height: 100px"><img src="{{ asset('uploads/'.$v->cooked_file) }}" style="width: 70px" /></div></td>
                                                    <td>{{ App\TradeQueriesINR::$tradeStatus[$v->status] }}</td>


                                                    <td style="text-align: center;">
                                                        @if($v->status != 2 && $v->status != 3)
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' ,[ 'tradeid' => $v->id , 'status'=> 3]) }}">Sold</a>
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' , ['tradeid' => $v->id , 'status'=> 2]) }}">Expired</a>
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
                                                <th style="text-align: center ">OfferPrice</th>
                                                <th style="text-align: center ">ValidDays</th>
                                                <th style="text-align: center ">Packing File</th>
                                                <th style="text-align: center ">Uncooked File</th>
                                                <th style="text-align: center ">Cooked File</th>
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