@extends('layouts.main')

    @section('content')
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    Notification
                    <small>Push Notification</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Notification</a></li>
                    <li class="active">Notification</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <form method="POST" action="{{ route('post.push.notification') }}">
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
                                            <label><input type="checkbox" name="userAppType" value="usd"> USD</label>
                                        </div>        
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="userAppType" value="inr"> INR</label>
                                        </div>        
                                    </div>  
                                    <div class="col-md-2"></div>
                                </div>
                            </div>

                            <div class="row" style="padding: 10px 0px;">
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
                            @enderror
                            

                            <div class="form-group">
                                <label for="comment">Notification Title:</label>
                                <input type="text" class="form-control" name="title">
                            </div>
                            @error('title')
                                <span class="" style="color: red">
                                    Please select all required fields.
                                </span>
                            @enderror
                            

                            <div class="form-group">
                                <label for="comment">Message:</label>
                                <textarea class="form-control" name="message" rows="5"></textarea>
                            </div>
                            @error('message')
                                <span class="" style="color: red">
                                    Please select all required fields.
                                </span>
                            @enderror

                            <button type="submit" name="submit" value="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    @endsection

    @section('scripts')
        <script type="text/javascript" src="{{ asset('js/deals.js') }}"></script>
    @endsection
