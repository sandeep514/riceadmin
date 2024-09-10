@extends('layouts.main')

@section('content')
<style type="text/css">
    .specifications{
        border: 1px solid #ccc;
    }
    .pd-0{
        padding: 0;
        margin-top: 4%
    }
    .center{
        text-align: center
    }
    .p-0{
        padding: 0;
    }
    .m-0{
        margin: 0;
    }
    .spec{
        padding: 11px;
        border-bottom: 1px solid #ccc;
        margin-bottom: 15px;
    }
    .attachmentImmage ul{
        list-style: none;
    }
</style>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Gallery
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Gallery</a></li>
                <li class="active">Upload</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Gallery</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'gallery.update','files'=>true]) !!}
                        <input type="hidden" name="id" value="{{ $gallery->id }}"> 
                        <div class="col-md-5">
                            <div class="form-group @error('title') has-error @enderror">
                                {!! Form::label('title','Title*') !!}
                                {!! Form::text('title',$gallery->title,['class'=>'form-control','id'=>'title',"required" => 'required' ,'placeholder'=>'Title']) !!}
                                @error('title')
                                    <span class="help-block text-danger" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group @error('desc') has-error @enderror">
                                {!! Form::label('desc','Description*') !!}
                                {!! Form::textarea('desc',$gallery->description,['class'=>'form-control', 'rows' => '3','id'=>'desc',"required" => 'required' ,'placeholder'=>'Description']) !!}
                                @error('desc')
                                    <span class="help-block text-danger" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                 <label for="type"> Rice Type</label>
                                 <select name="type" class="form-control" value="{{ $gallery->type }}">
                                     <option value="basmati"    {{ ($gallery->type == 'basmati')? 'selected' : '' }}>Basmati</option>
                                     <option value="nonbasmati" {{ ($gallery->type == 'nonbasmati') ? 'selected' : '' }}>Non Basmati</option>
                                 </select>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group col-md-3 @error('file') has-error @enderror" style="padding: 0">
                                        {!! Form::label('file','File*') !!}
                                        {!! Form::file('file',null,['class'=>'form-control','id'=>'file']) !!}
                                        @error('file')
                                            <span class="help-block text-danger" role="alert">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                        <div class="attachmentImmage">
                                            <ul>
                                                <li>
                                                    <img src="{{ asset('uploads/gallery/'.$gallery->attachment) }}" style="width: 100px">
                                                </li>
                                                <!--<li>-->
                                                <!--    <button class="btn btn-danger btn-sm" style="width: 100px">Delete</button>-->
                                                <!--</li>-->
                                            </ul>    
                                        </div>
                                        
                                        
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group col-md-6 @error('file2') has-error @enderror" style="padding: 0">
                                        {!! Form::label('file2','Second File*') !!}
                                        {!! Form::file('file2',null,['class'=>'form-control','id'=>'file2']) !!}
                                        @error('file2')
                                            <span class="help-block text-danger" role="alert">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                        <div class="attachmentImmage">
                                            <ul>
                                                <li>
                                                    <img src="{{ asset('uploads/gallery/'.$gallery->attachment2) }}" style="width: 100px">
                                                </li>
                                                <!--<li>-->
                                                <!--    <button class="btn btn-danger btn-sm" style="width: 100px">Delete</button>-->
                                                <!--</li>-->
                                            </ul>    
                                        </div>
                                    </div>
                                  
                                    
                                </div>
                            </div>

                            <div class="row specifications">
                                
                                <div class="rows">
                                    <h3 class="center p-0 m-0 spec">
                                        Add Specifications
                                    </h3>
                                    <div class="col-md-5">
                                        <div class="form-group ">
                                            {!! Form::label("key","Key*") !!}
                                            {!! Form::text("key[]",null,["class"=>"form-control","id"=>"key","placeholder"=>"Due Days"]) !!}
                                            @error("key")
                                                <span class="help-block text-danger" role="alert">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>                                    
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            {!! Form::label("value","Value*") !!}
                                            {!! Form::text("value[]",null,["class"=>"form-control","id"=>"value","placeholder"=>"Due Days"]) !!}
                                            @error("value")
                                                <span class="help-block text-danger" role="alert">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>  
                                    </div>
                                    
                                    
                                    <div class="col-md-1 pd-0" >
                                        <a href="javascript:void(0);" class="addNew btn btn-primary">Add</a>
                                    </div>
                                    
                                    {{-- @if( $gallery->spec != null )
                                        @php
                                            $gallerySpec = json_decode($gallery->spec , true);
                                        @endphp
                                        @foreach($gallerySpec as $k => $v)
                                            <div>
                                                <div class="col-md-5">
                                                    <div class="form-group ">
                                                        {!! Form::label("key","Key*") !!}
                                                        {!! Form::text("key[]",$k,["class"=>"form-control","id"=>"key","required" => 'required' ,"placeholder"=>"Due Days"]) !!}
                                                        @error("key")
                                                            <span class="help-block text-danger" role="alert">
                                                                {{ $message }}
                                                            </span>
                                                        @enderror
                                                    </div>                                    
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="form-group ">
                                                        {!! Form::label("value","Value*") !!}
                                                        {!! Form::text("value[]",$v,["class"=>"form-control","id"=>"value","required" => 'required' ,"placeholder"=>"Due Days"]) !!}
                                                        @error("value")
                                                            <span class="help-block text-danger" role="alert">
                                                                {{ $message }}
                                                            </span>
                                                        @enderror
                                                    </div>  
                                                </div>
                                                <div class="col-md-1">
                                                    <button class="subold btn btn-danger">-</button>
                                                </div>
                                            </div>
                                            
                                        @endforeach
                                    @endif --}}
                                    


                                </div>

                                <div class="columnrow">
                                    @if( $gallery->spec != null )
                                        @php
                                            $gallerySpec = json_decode($gallery->spec , true);
                                        @endphp
                                        @foreach($gallerySpec as $k => $v)
                                            <div class="addedRow">
                                                <div class="col-md-5">
                                                    <div class="form-group "> 
                                                        {!! Form::label("key","Key*") !!} 
                                                        {!! Form::text("key[]",$k,["class"=>"form-control","id"=>"key" ,"placeholder"=>"Due Days"]) !!} 
                                                        @error("key") 
                                                            <span class="help-block text-danger" role="alert"> {{ $message }} </span>
                                                        @enderror 
                                                    </div>
                                                </div> 
                                                <div class="col-md-6"> 
                                                    <div class="form-group "> 
                                                        {!! Form::label("value","Value*") !!} 
                                                        {!! Form::text("value[]",$v,["class"=>"form-control","id"=>"value" ,"placeholder"=>"Due Days"]) !!} 
                                                        @error("value") 
                                                            <span class="help-block text-danger" role="alert"> {{ $message }} </span> 
                                                        @enderror 
                                                    </div>
                                                </div> 
                                                <div class="col-md-1 pd-0" > 
                                                    <button class="subold btn btn-danger">-</button> 
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="{{ route('documents') }}" class="btn btn-danger">Cancel</a>
                                    </div>      
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>

                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('.addNew').click(function(){
                var row = '<div class="addedRow"><div class="col-md-5"> <div class="form-group "> {!! Form::label("key","Key*") !!} {!! Form::text("key[]",null,["class"=>"form-control","id"=>"key" ,"placeholder"=>"Due Days"]) !!} @error("key") <span class="help-block text-danger" role="alert"> {{ $message }} </span> @enderror </div> </div> <div class="col-md-6"> <div class="form-group "> {!! Form::label("value","Value*") !!} {!! Form::text("value[]",null,["class"=>"form-control","id"=>"value","placeholder"=>"Due Days"]) !!} @error("value") <span class="help-block text-danger" role="alert"> {{ $message }} </span> @enderror </div> </div> <div class="col-md-1 pd-0" > <button class="subold btn btn-danger">-</button> </div></div>';

                $('.columnrow').append(row);
            });
            $(document).on('click', '.subold' , function(){
                $(this).parents('.addedRow').remove();
            });
        });
    </script>
@endsection

