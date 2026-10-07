@extends('layouts.main')

    @section('content')
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    Push Notification
                    <small>Send</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('send.push.notification') }}">Push Notification</a></li>
                    <li class="active">Send</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">Queue Status (queue: {{ $pushQueue ?? 'notifications' }})</h3>
                            </div>
                            <div class="box-body">
                                @if(is_null($pendingJobs) && is_null($failedJobs))
                                    <span class="text-muted">Queue tables unavailable — QUEUE_CONNECTION is probably set to sync.</span>
                                @else
                                    <span class="label {{ ($pendingJobs ?? 0) > 0 ? 'label-danger' : 'label-success' }}">
                                        Pending: {{ $pendingJobs ?? '?' }}
                                    </span>
                                    &nbsp;
                                    <span class="label {{ ($failedJobs ?? 0) > 0 ? 'label-warning' : 'label-success' }}">
                                        Failed: {{ $failedJobs ?? '?' }}
                                    </span>
                                    @if(($pendingJobs ?? 0) > 0)
                                        <p class="text-danger" style="margin-top: 8px;">
                                            Jobs are waiting but no worker is processing the "{{ $pushQueue }}" queue.
                                            Run: <code>php artisan queue:work database --queue={{ $pushQueue }},default --sleep=3 --tries=3 --timeout=300</code>
                                        </p>
                                    @endif
                                    @if(!empty($latestFailure))
                                        <p class="text-muted" style="margin-top: 8px;">
                                            Latest failure #{{ $latestFailure->id }} at {{ $latestFailure->failed_at }}:<br>
                                            <code>{{ $latestFailure->excerpt ?? '' }}</code>
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('post.push.notification') }}" id="push-notification-form">
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
                                            <label><input type="checkbox" name="userAppType[]" value="usd"> USD</label>
                                        </div>        
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="userAppType[]" value="inr"> INR</label>
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

                            <button type="submit" name="submit" value="submit" id="push-notification-submit">Submit</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    @endsection

    @section('scripts')
        <script type="text/javascript" src="{{ asset('js/deals.js') }}"></script>
        <script type="text/javascript">
            (function () {
                var form = document.getElementById('push-notification-form');
                var submitBtn = document.getElementById('push-notification-submit');
                if (!form || !submitBtn) {
                    return;
                }
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Sending…';
                });
            })();
        </script>
    @endsection
