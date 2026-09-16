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
                        <form method="POST" action="{{ route('web.master.post.news.runner') }}">
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
                                            <label><input type="checkbox" name="type[]" value="inr" checked> INR</label>
                                        </div>         
                                    </div>  
                                    <div class="col-md-2"></div>
                                </div>

                            </div>
                            <div class="row" style="border-bottom: 2px solid #fff;padding-bottom: 10px"> 

                                <div class="row">
                                    <div class="col-md-12">
                                        <p>News Type</p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-4">
                                        <div class="radio">
                                            <label><input type="radio" name="newsType" value="recent" checked> Recent</label>
                                        </div>        
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="radio">
                                            <label><input type="radio" name="newsType" value="sntc"> SNTC</label>
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
                                <label for="news_date">News Date</label>
                                <input type="date" class="form-control" id="news_date" name="news_date" value="{{ old('news_date', date('Y-m-d')) }}" autocomplete="off" required>
                            </div>
                            @error('news_date')
                                <span class="" style="color: red">
                                    {{ $message }}
                                </span>
                            @enderror

                            <div class="form-group">
                                <label for="title">Title <small class="text-muted">(optional)</small></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
                            </div>
                            @error('description')
                                <span class="" style="color: red">
                                    {{ $message }}
                                </span>
                            @enderror
                            @error('title')
                                <span class="" style="color: red">
                                    {{ $message }}
                                </span>
                            @enderror
                            @error('type')
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
                                    <table id="web-news-runner-table" class="display" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center ">Title</th>
                                                <th style="text-align: center ">Description</th>
                                                <th style="text-align: center ">Type</th>
                                                <th style="text-align: center ">News Type</th>
                                                <th style="text-align: center ">News Date</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">created at</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse($news as $v)
                                                <tr>
                                                    <td>{{ $v->title ?: '-' }}</td>
                                                    <td>{{ $v->description }}</td>
                                                    <td>{{ $v->type }}</td>
                                                    <td>{{ $v->newsType }}</td>
                                                    <td>{{ $v->news_date ? \Carbon\Carbon::parse($v->news_date)->format('d-m-Y') : '-' }}</td>
                                                    <td>{{ ($v->status==1)?'Active' : 'De-active' }}</td>
                                                    <td>{{ $v->created_at }}</td>


                                                    <td style="text-align: center;">

                                                        @if($v->status == 2)
                                                            <a class="btn btn-info btn-sm" href="{{ route('web.master.news.change.status' ,[ 'newsId' => $v->id , 'status'=> 1]) }}">Activate</a>
                                                        @endif
                                                        
                                                        @if($v->status == 1)
                                                            <a class="btn btn-danger btn-sm" href="{{ route('web.master.news.change.status' ,[ 'newsId' => $v->id , 'status'=> 2]) }}">De-Active</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">No news found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Title</th>
                                                <th style="text-align: center ">Description</th>
                                                <th style="text-align: center ">Type</th>
                                                <th style="text-align: center ">News Type</th>
                                                <th style="text-align: center ">News Date</th>
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">created at</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <div class="text-center" style="margin-top: 10px;">
                                        <p>Page {{ $news->currentPage() }} of {{ $news->lastPage() }} — Total {{ $news->total() }}</p>
                                        {{ $news->onEachSide(1)->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
            </section>
        </div>
    @endsection

    @section('scripts')
        <script type="text/javascript" src="{{ asset('js/deals.js') }}"></script>
    @endsection
