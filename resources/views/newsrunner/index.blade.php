@extends('layouts.main')

    @section('content')
    <style type="text/css">
        td{
            text-align: center;
        }
    </style>
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    News runner
                    <small>Push news runner</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> News runner</a></li>
                    <li class="active">News runner</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <form method="POST" action="{{ route('master.post.news.runner') }}">
                            {{ csrf_field() }}

                            <div class="row" style="border-bottom: 2px solid #fff;padding-bottom: 10px"> 

                                <div class="row">
                                    <div class="col-md-12">
                                        <p>User App Type</p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="type[]" value="usd"> USD</label>
                                        </div>        
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="type[]" value="inr"> INR</label>
                                        </div>        
                                    </div>  
                                    <div class="col-md-2"></div>
                                </div>

                            </div>

                            {{--    <div class="row" style="padding: 10px 0px;">
                                <div class="col-md-3">
                                    <div class="checkbox">
                                        <label><input type="checkbox" name="userType[]" value="5"> Buyer</label>
                                    </div>        
                                </div>
                                <div class="col-md-3">
                                    <div class="checkbox">
                                        <label><input type="checkbox" name="userType[]" value="6"> Supplier</label>
                                    </div>        
                                </div>
                                <div class="col-md-3">
                                    <div class="checkbox">
                                        <label><input type="checkbox" name="userType[]" value="7"> Broker</label>
                                    </div>        
                                </div>
                                <div class="col-md-3">
                                    <div class="checkbox">
                                        <label><input type="checkbox" name="userType[]" value="8"> Guest</label>
                                    </div>        
                                </div>
                            </div>

                            @error('userType')
                                <span class="" style="color: red">
                                    Please select all required fields.
                                </span>
                            @enderror --}}

                            <div class="form-group">
                                <label for="comment">Runner:</label>
                                <input type="text" class="form-control" name="title">
                            </div>
                            @error('title')
                                <span class="" style="color: red">
                                    Please select all required fields.
                                </span>
                            @enderror
                            
                            <button type="submit" name="submit" value="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </section>
            <section>
                <div class="box-body">
                            <div class="table-responsive">
                                <div class="col-md-12">
                                    <table id="example2" class="display" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center ">Title</th>
                                                <th style="text-align: center ">Type</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">created at</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($news as $k => $v)
                                                <tr>
                                                    <td>{{ $v->title }}</td>
                                                    <td>{{ $v->type }}</td>
                                                    <td>{{ ($v->status==1)?'Active' : 'De-active' }}</td>
                                                    <td>{{ $v->created_at }}</td>


                                                    <td style="text-align: center;">

                                                        @if($v->status == 2)
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.news.change.status' ,[ 'newsId' => $v->id , 'status'=> 1]) }}">Activate</a>
                                                        @endif
                                                        
                                                        @if($v->status == 1)
                                                            <a class="btn btn-danger btn-sm" href="{{ route('master.news.change.status' ,[ 'newsId' => $v->id , 'status'=> 2]) }}">De-Active</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Title</th>
                                                <th style="text-align: center ">Type</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">created at</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
            </section>
        </div>
    @endsection

    @section('scripts')
        <script type="text/javascript" src="{{ asset('js/deals.js') }}"></script>
    @endsection
