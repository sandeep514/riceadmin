@extends('layouts.main')
    @section('content')
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    Notification
                    <small>Push Notifications</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Gallery</a></li>
                    <li class="active">Gallery</li>
                </ol>
            </section>
            <section class="content" style="margin-bottom: 30px;">
                <div class="row">
                    <div class="col-xs-12">
                        <form method="POST" action="{{ route('post.hot.deal.push.notification') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="row">
                                
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label for="comment">Notification Title*:</label>
                                    <input value="{{ old('title') }}" type="text" class="form-control" name="title">
                                </div>    
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label for="comment">Quality*:</label>
                                    <select class="form-control" name="quality">
                                        @foreach( $quality as $k => $v ) 
                                            <option value="{{ $v->id }}">{{ $v->quality }} {{ $v->quality_name }} ({{ $v->quality_type }})</option>
                                        @endforeach
                                    </select>
                                </div>                              
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label for="comment">Packing*:</label>
                                    <select class="form-control" name="packing">
                                        @foreach( $packing as $k => $v ) 
                                            <option value="{{ $v->id }}">{{ $v->bag_size }} {{ $v->bag_type }} ({{ ($v->applied_for == 0) ? 'Basmati' : 'Non-Basmati' }})</option>
                                        @endforeach
                                    </select>
                                </div>                              
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label for="comment">FOB*:</label>
                                    <input value="{{ old('fob') }}" type="text" class="form-control" name="fob">
                                </div>                              
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label for="comment">Qty*:</label>
                                    <input value="{{ old('qty') }}" type="text" class="form-control" name="qty">
                                </div>                              

                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label for="comment">Valid Date*:</label>
                                    <input value="{{ old('validdate') }}" type="datetime-local" name="validdate" class="form-control" min="{{ $todayDate }}">
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label for="comment">Message*:</label>
                                    <textarea class="form-control" name="message" rows="5">{{ old('message ') }}</textarea>
                                </div>
                                <div class="form-group col-md-6 col-sm-6 col-xs-6">
                                    <label for="comment">Attachment1:</label>
                                    <input type="file" name="attachment1" class="form-control">
                                </div>
                                <div class="form-group col-md-6 col-sm-6 col-xs-6">
                                    <label for="comment">Attachment2:</label>
                                    <input type="file" name="attachment2" class="form-control">
                                </div>

 





                                <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                    <label for="comment">Length:</label>
                                    <input type="text" name="Length" placeholder="Length" class="form-control">
                                </div>
                                <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                    <label for="comment">Purity:</label>
                                    <input type="text" name="Purity" placeholder="Purity" class="form-control">
                                </div>
                                <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                    <label for="comment">Moisture:</label>
                                    <input type="text" name="Moisture" placeholder="Moisture" class="form-control">
                                </div>
                                <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                    <label for="comment">Broken:</label>
                                    <input type="text" name="Broken" placeholder="Broken" class="form-control">
                                </div>
                                <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                    <label for="comment">Kett:</label>
                                    <input type="text" name="Kett" placeholder="Kett" class="form-control">
                                </div>
                                <div class="form-group col-md-4 col-sm-4 col-xs-4">
                                    <label for="comment">DDs:</label>
                                    <input type="text" name="DDs" placeholder="DDs" class="form-control">
                                </div>
                            </div>

                            @if($errors->any())
                                <span class="" style="color: red">
                                    Please select all required fields*.
                                </span>
                            @endif

                            <button type="submit" name="submit" value="submit" class="btn btn-info">Save Hot Notification</button>
                        </form>
                    </div>
                </div>
            </section>
            <section>
                 <div class="col-md-12">
                        <table id="example2" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center">Quality</th>
                                    <th style="text-align: center">Packing</th>
                                    <th style="text-align: center">Qty</th>
                                    <th style="text-align: center">FOB</th>
                                    <th style="text-align: center">Title</th>
                                    <th style="text-align: center">Message </th>
                                    <th style="text-align: center">Valid Date</th>
                                    <th style="text-align: center">Action</th>

                                </tr>
                            </thead>

                            <tbody>
                                @foreach($hotDeal as $k => $v)
                                    <tr>
                                        <td style="text-align: center">{{ ($v->getUSDDefaultMaster != null) ? $v['getUSDDefaultMaster']['bag_size'].' '.$v['getUSDDefaultMaster']['bag_type'] : '' }}</td>
                                        <td style="text-align: center">{{ ($v->getRiceQuality != null) ? $v['getRiceQuality']['quality'].' '.$v['getRiceQuality']['quality_name'].' '.($v['getRiceQuality']['quality_type']) : '' }}</td>
                                        <td style="text-align: center">{{ $v->qty }}</td>
                                        <td style="text-align: center">{{ $v->fob }}</td>
                                        <td style="text-align: center">{{ $v->title }}</td>
                                        <td style="text-align: center">{{ $v->message  }}</td>
                                        <?php
                                            $date=date_create($v->validDate);
                                            
                                        ?>
                                        <td style="text-align: center">{{ date_format($date,"d-m-Y H:i:s") }}</td>
                                        <td style="text-align: center">
                                            @if( $v->status == 1 )
                                                <a href="javascript:void(0)" attrhref="{{ route('update.hot.deal.status' , ['2',$v->id])}}" class="confirmPopupSold btn btn-xs btn-info" value="{{$v->id}}">Sold</a>
                                                <a href="javascript:void(0)" attrhref="{{ route('update.hot.deal.status' , ['0',$v->id])}}" class="confirmPopuptaken btn btn-xs btn-danger" value="{{$v->id}}">Taken</a>
                                            @endif
                                            @if($v->status == 0)
                                                <span>Taken</span>
                                            @endif
                                            @if($v->status == 2)
                                                <span>Sold</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            
                            <tfoot>
                                <tr>
                                    <th style="text-align: center">Quality</th>
                                    <th style="text-align: center">Packing</th>
                                    <th style="text-align: center">Qty</th>
                                    <th style="text-align: center">FOB</th>
                                    <th style="text-align: center">Title</th>
                                    <th style="text-align: center">Message </th>
                                    <th style="text-align: center">Valid Date</th>
                                    <th style="text-align: center">Action</th>

                                </tr>
                            </tfoot>
                        </table>
                    </div>
            </section>
        </div>
    @endsection

    @section('scripts')
        <script type="text/javascript" src="{{ asset('js/deals.js') }}"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on('click' , '.confirmPopupSold' , function(){
                    let attrhref = $(this).attr('attrhref');

                    if (confirm("Are you sure?") == true) {
                        window.location.href = attrhref
                    } else {
                        return 'false';
                    }
                })
                $(document).on('click' , '.confirmPopuptaken' , function(){
                    let attrhref = $(this).attr('attrhref');

                    if (confirm("Are you sure?") == true) {
                        window.location.href = attrhref
                    } else {
                        return 'false';
                    }
                })
            })
        </script>
    @endsection
