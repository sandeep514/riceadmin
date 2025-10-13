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
                    Grade
                    <small>Grade</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Grade</a></li>
                    <li class="active">Grade</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <form method="POST" action="{{ route('save.rice.grade') }}">
                            {{ csrf_field() }}

                            <div class="form-group">
                                <label for="comment">Runner:</label>
                                <input type="text" class="form-control" name="name">
                            </div>
                            @error('name')
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
                                                <th style="text-align: center ">Status</th>
                                                <th style="text-align: center ">created at</th>
                                                <th style="text-align: center ">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($grade as $k => $v)
                                                <tr>
                                                    <td>{{ $v->name }}</td>
                                                    <td>{{ ($v->status==1)?'Active' : 'De-active' }}</td>
                                                    <td>{{ $v->created_at }}</td>


                                                    <td style="text-align: center;">

                                                       <!--  @if($v->status == 2)
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.news.change.status' ,[ 'newsId' => $v->id , 'status'=> 1]) }}">Activate</a>
                                                        @endif
                                                        
                                                        @if($v->status == 1)
                                                            <a class="btn btn-danger btn-sm" href="{{ route('master.news.change.status' ,[ 'newsId' => $v->id , 'status'=> 2]) }}">De-Active</a>
                                                        @endif -->
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Title</th>
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
