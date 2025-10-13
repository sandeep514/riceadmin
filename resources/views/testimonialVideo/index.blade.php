@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Testimonial
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Testimonial</a></li>
                <li class="active">Dashboard</li>
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
                                        <a href="{{ route('testimonial.video.create') }}" class="btn btn-info btn-sm" style="float: right">Create</a>
                                        <div class="col-md-12 inputs">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Attachment</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($testimonial as $key => $value)
                                                        <tr>
                                                            <td style="text-transform: capitalize;">{{ $value->title }}</td>
                                                            <td style="text-transform: capitalize;">
                                                                <video width="220" height="140" controls>
                                                                    <source src="{{ asset('uploads/testimonial/video/'.$value->file) }}" type="video/mp4">
                                                                </video>
                                                            </td>
                                                           
                                                            <td>
                                                                <ul style="list-style: none;display: inline-flex;padding: 0">
                                                                
                                                                 <!--    <li style="margin-left: 20px">
                                                                        <a class="btn btn-sm btn-info" href="{{ route('testimonial.edit' , base64_encode($value->id)) }}"> Update </a>
                                                                    </li> -->
                                                                </ul>
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