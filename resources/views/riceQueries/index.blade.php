@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Qualities
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Qualities</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Quality of roles</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <div class="col-md-12">
                                    <table id="example2" class="display" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center ">Party Name</th>
                                                <th style="text-align: center ">Party Mobile</th>
                                                <th style="text-align: center ">Quality</th>
                                                <th style="text-align: center ">Remarks</th>
                                                <th style="text-align: center ">Rice min & max </th>
                                                <th style="text-align: center ">Packing</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($buyQueries as $k => $v)
                                                <tr>
                                                    <td>{{ $v->partyName }}</td>
                                                    <td>{{ $v->mobile }}</td>
                                                    <td>{{ $v->qualityName }}</td>
                                                    <td>{{ $v->remarks }}</td>
                                                    <td>{{ $v->quantity }}</td>
                                                    <td>{{ $v->qualityType  }}</td>

                                                    <td style="text-align: center;">
                                                        @if($v->getBids->count() > 0)
                                                            <a href="#" data-toggle="modal" data-target="#exampleModal_{{ $v->id }}" class="btn btn-xs btn-info"> View Bids </a>

                                                            <div class="modal fade" id="exampleModal_{{ $v->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                              <div class="modal-dialog" role="document" style="width: 70%">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLabel">Bid Info</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <table id="example2" class="display" style="width: 100%;">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th style="text-align: center ">Seller Name</th>
                                                                                    <th style="text-align: center ">Seller Email</th>
                                                                                    <th style="text-align: center ">Seller Mobile </th>
                                                                                    <th style="text-align: center ">Amount</th>
                                                                                    <th style="text-align: center ">Counter Amount</th>
                                                                                    <th style="text-align: center ">Counter Amount status</th>
                                                                                    <th style="text-align: center ">Action</th>
                                                                                </tr>
                                                                            </thead>

                                                                            <tbody>
                                                                                @foreach($v->getBids as $ke => $va)
                                                                                    @if($va->has('seller'))
                                                                                        <tr>
                                                                                            <td>{{ $va->seller->name }}</td>
                                                                                            <td>{{ $va->seller->email }}</td>
                                                                                            <td>{{ $va->seller->mobile }}</td>
                                                                                            <td>{{ $va->bid_amount }}</td>
                                                                                            <td>{{ $va->counter_amount }}</td>
                                                                                            <td>{{ ($va->counter_sttaus == 0) ? 'Not yet accepted' : (($va->counter_status == 1) ? 'Accepted' : "Rejected") }}</td>
                                                                                            <td>
                                                                                                <form method="POST" action="{{ route('send.seller.confirm.message') }}">
                                                                                                    @csrf
                                                                                                    <input type="text" name="price">
                                                                                                    <input type="hidden" name="user_id" value="{{$va->seller->id}}">
                                                                                                    <input type="hidden" name="bid_id" value="{{ $v->id }}">
                                                                                                    <input type="hidden" name="vendor_id" value="">
                                                                                                    <input type="hidden" name="buyer_id" value="">
                                                                                                    <input type="hidden" name="negotiation_amount" value="">

                                                                                                    <input type="submit" name="submit" class="btn btn-xs btn-primary" value="Send Counter Amount">
                                                                                                </form>
                                                                                            </td> 
                                                                                        </tr>
                                                                                    @endif
                                                                                @endforeach
                                                                            </tbody>
                                                                            
                                                                            <tfoot>
                                                                                <tr>
                                                                                    <th style="text-align: center ">Seller Name</th>
                                                                                    <th style="text-align: center ">Seller Email</th>
                                                                                    <th style="text-align: center ">Seller Mobile </th>
                                                                                    <th style="text-align: center ">Amount</th>
                                                                                    <th style="text-align: center ">Counter Amount</th>
                                                                                    <th style="text-align: center ">Counter Amount Status</th>
                                                                                    <th style="text-align: center ">Action</th>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>

                                                                    </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                    <button type="button" class="btn btn-primary">Save changes</button>
                                                                </div>
                                                                </div>
                                                              </div>
                                                            </div>
                                                        @else
                                                            <div>
                                                                <p style="font-size: 12px;">No Bids Available Yet</p>           
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Party Name</th>
                                                <th style="text-align: center ">Party Mobile</th>
                                                <th style="text-align: center ">Quality</th>
                                                <th style="text-align: center ">Remarks</th>
                                                <th style="text-align: center ">Rice min & max </th>
                                                <th style="text-align: center ">Packing</th>
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
