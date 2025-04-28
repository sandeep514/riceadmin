@extends('layouts.main')

    @section('content')
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    Gallery
                    <small>List</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Gallery</a></li>
                    <li class="active">Gallery</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">List of Gallery</h3>
                            </div>
                            
                            <div class="box-body">
                                <div class="table-responsive">
                                     {{-- {!! $dataTable->table(['class'=>'table table-bordered table-striped datatable','width'=>'100%']) !!} --}}
                                </div>
                                
                                <table id="example2" class="display" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center">Title</th>
                                            <th style="text-align: center">Description </th>
                                            <th style="text-align: center">Attachment</th>
                                            <th style="text-align: center">Attachment2</th>
                                            <th style="text-align: center">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($gallery as $k => $v)
                                            <tr>
                                                <td style="text-align: center">{{ $v->title }}</td>
                                                <td style="text-align: center">{{ $v->description  }}</td>
                                                <td style="text-align: center">
                                                    <img src="{{ asset('uploads/gallery/'.$v->attachment)  }}" style="width: 50px; height: 50px;" />
                                                </td>
                                                <td style="text-align: center">
                                                    <img src="{{ asset('uploads/gallery/'.$v->attachment2)  }}" style="width: 50px; height: 50px;" />
                                                </td>
                                                <td style="text-align: center">
                                                    <a href="{{ route('gallery.delete' , [ 'id' => $v->id ] )}}" >Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    
                                    <tfoot>
                                        <tr>
                                            <th style="text-align: center">Title</th>
                                            <th style="text-align: center">Description</th>
                                            <th style="text-align: center">Attachment</th>
                                            <th style="text-align: center">Attachment2</th>
                                            <th style="text-align: center">Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endsection

    @section('scripts')
        {{-- {!! $dataTable->scripts() !!} --}}
        <script type="text/javascript" src="{{ asset('js/deals.js') }}"></script>
    @endsection
