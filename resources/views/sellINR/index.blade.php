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
                                                <!-- <th style="text-align: center ">sno</th> -->
                                                <th style="text-align: center ">Quality Fype</th>
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
                                            @foreach($sellerQueries as $k => $v)
                                                <tr>
                                                    <!-- <td>{{ $v->id }}</td> -->
                                                    <td>{{ ($v->quality_type == 0)? 'Basmati' : 'Non-Basmati'  }}</td>
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
                                                    <td>{{ $v->status }}</td>

                                                    <td style="text-align: center;">
                                                       {{-- @if($v->getBids->count() > 0)
                                                            @if( $v->status == 2 )
                                                                <p> SOLD </p>
                                                            @endif
                                                            @if( $v->status != 2 )
                                                                <a href="{{ route('rice.query.master.sold' , $v->id) }}" class="btn btn-xs btn-success"> Sold </a>
                                                            @endif


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
                                                                                    @if($va->has('seller') && $va->seller != null)
                                                                                        <tr>
                                                                                            <td>{{ $va->seller->name }}</td>
                                                                                            <td>{{ $va->seller->email }}</td>
                                                                                            <td>{{ $va->seller->mobile }}</td>
                                                                                            <td>{{ $va->bid_amount }}</td>
                                                                                            <td>{{ $va->counter_amount }}</td>
                                                                                            <td>{{ ($va->counter_status == 0) ? ($va->accept_status != 1)?'Not yet accepted' : 'You Accept the bid' : (($va->counter_status == 1) ? 'Accepted' : "Rejected") }}</td>

                                                                                            @if($va->counter_status == 0)
                                                                                                <td>
                                                                                                    @if( $va->accept_status != 1 ) 
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
                                                                                                    @endif
                                                                                                    @if( $va->accept_status != 1 ) 
                                                                                                        <a href="{{ route('rice.query.master.accept' , ['id' => $va->id]) }}" class="btn btn-info btn-xs" >Accept Offer</a>
                                                                                                    @endif
                                                                                                </td>
                                                                                            @endif
                                                                                             
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
                                                                <a href="{{ route('rice.query.master.sold' , $v->id) }}" class="btn btn-xs btn-success"> Sold </a>

                                                                <p style="font-size: 12px;">No Bids Available Yet</p> 
                                                                <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#myModal_{{$v->id}}">Edit Specs</button>

                                                                <div id="myModal_{{$v->id}}" class="modal fade" role="dialog">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                                <h4 class="modal-title">Update Spec</h4>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <form action="{{ route('update.rice.query.master') }}" method="POST">
                                                                                    @csrf
                                                                                    <input type="hidden" name="buyqueryid" value="{{ $v->id }}">
                                                                                    <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                                                                        <label for="comment">Length:</label>
                                                                                        <input type="text" value="{{ $v->length }}" name="Length" placeholder="Length" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                                                                        <label for="comment">Purity:</label>
                                                                                        <input type="text" value="{{ $v->purity }}" name="Purity" placeholder="Purity" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                                                                        <label for="comment">Moisture:</label>
                                                                                        <input type="text" value="{{ $v->moisture }}" name="Moisture" placeholder="Moisture" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                                                                        <label for="comment">Broken:</label>
                                                                                        <input type="text" value="{{ $v->broken }}" name="Broken" placeholder="Broken" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                                                                        <label for="comment">Kett:</label>
                                                                                        <input type="text" value="{{ $v->kett }}" name="Kett" placeholder="Kett" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                                                                        <label for="comment">DDs:</label>
                                                                                        <input type="text" value="{{ $v->dd }}" name="DDs" placeholder="DDs" class="form-control">
                                                                                    </div>
                                                                                    <input type="submit" class="btn btn-sm btn-primary" value="update Spec">
                                                                                </form>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>          
                                                            </div>
                                                        @endif


                                                        @if($v->status != 2)
                                                            @if($v->status != 1)
                                                                <a href='{{ route("activate.query" , $v->id) }}' class="btn btn-primary btn-xs" >Active</a>
                                                            @endif
                                                        @endif
 --}}
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Quality Fype</th>
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
