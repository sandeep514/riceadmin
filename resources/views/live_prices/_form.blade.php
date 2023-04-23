<style>
    .nonbasmatitabs .nav>li>a {
        padding: 10px 11px;
    }    
    .basmatitabs .nav>li>a {
        padding: 10px 11px;
    }
</style>
<div class="box-body">
    <div class="row">
        <div class="form-group col-md-12 @error('name') has-error @enderror">
            {!! Form::label('name','Name*') !!}
            {!! Form::select('name',\App\RiceName::qualityNamesForLivePrice(),request()->rice_name,['class'=>'form-control','id'=>'category','placeholder'=>'Select Name']) !!}
            
            @error('name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
            
        </div>
    </div>
    @if($riceModel != null)
        @if($riceModel->type == 'basmati')
            <div class="responsiveTabs basmatitabs">
                <ul id="myTab" class="nav nav-tabs" style="margin-bottom: 15px;">

                    @foreach( $livePrice as $k => $v )
                        <li class="">
                            <a href="#model{{ str_replace(' ' , '_' , $k) }}" data-toggle="tab">{{ str_replace(' ','_', $k) }}</a>
                        </li>
                    @endforeach
                </ul>
                <div id="myTabContent" class="tab-content" >
                    @foreach( $livePrice as $keyy => $val )
                        <div class="tab-pane fade in " id="model{{ str_replace(' ' , '_' , $keyy) }}">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h3>Price Details</h3>
                                    <b>Rice Name: </b> {{ $riceModel->name }}                                   
                                        <div class="row text-left" style="margin-top: 20px;">
                                            <div class="col-md-12 inputs">
                                                {!! Form::label('na','Click For All NA: ') !!}
                                                {!! Form::checkbox('all_na',null,null,['class'=>'check_for_na']) !!}
                                                <table class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th>Rice Type</th>
                                                        <th>Min Price</th>
                                                        <th>Max Price</th>
                                                        <th>Up/Down</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($riceForm as $key => $form)
                                                        @php
                                                            $min = null;
                                                            $max = null;
                                                            $upDown = null;
                                                            
                                                            if($lastPrices != null){
                                                                $details = $lastPrices->where('form',$form->id)->where('state', str_replace(' ' , '_' , $keyy) )->first();
                                                                if($details != null){
                                                                    $min = $details->min_price;
                                                                    $max = $details->max_price;
                                                                    $upDown = $details->up_down;
                                                                }
                                                            }
                                                            
                                                        @endphp
                                                            <tr>
                                                                <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                                <td>
                                                                    {!! Form::text('min['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                                </td>
                                                                <td>
                                                                    {!! Form::text('max['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                                </td>
                                                                <td>
                                                                    {!! Form::select('up_down['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
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
                    @endforeach
                </div>
            </div>
        @else
            <div class="responsiveTabs nonbasmatitabs">
                <ul id="myTab" class="nav nav-tabs" style="margin-bottom: 15px;">
                    @foreach( $livePrice as $k => $v )
                        
                        <li class="">
                            <a href="#model{{ str_replace(' ' , '_' , $k) }}" data-toggle="tab">{{ str_replace(' ' , '_' , $k) }}</a>
                        </li>
                    @endforeach
                </ul>
                <div id="myTabContent" class="tab-content " >
                    @foreach( $livePrice as $keyy => $val )

                        <div class="tab-pane fade in" id="model{{ str_replace(' ' , '_' , $keyy) }}">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h3>Price Details</h3>
                                    <b>Rice Name: </b> {{ $riceModel->name }}
                                    <div class="row text-left" style="margin-top: 20px;">
                                        <div class="col-md-12 inputs">
{{--                                             {!! Form::label('na','Click For All NA: ') !!}
                                            {!! Form::checkbox('all_na',null,null,['class'=>'check_for_na']) !!}
 --}}                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Rice Type</th>
                                                    <th>Min Price</th>
                                                    <th>Max Price</th>
                                                    <th>Up/Down</th>
                                                </tr>
                                                </thead>
                                                <tbody>


                                                @foreach($riceForm as $key => $form)
                                                    @php
                                                        $min = null;
                                                        $max = null;
                                                        $upDown = null;
                                                        if($lastPrices != null){
                                                            $details = $lastPrices->where('form',$form->id)->where('state', str_replace(' ' , '_' , $keyy))->first();
                                                            if($details != null){
                                                                $min = $details->min_price;
                                                                $max = $details->max_price;
                                                                $upDown = $details->up_down;
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                        <tr>
                                                            <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                            <td>
                                                                {!! Form::text('min['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                            </td>
                                                            <td>
                                                                {!! Form::text('max['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                            </td>
                                                            <td>
                                                                {!! Form::select('up_down['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
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
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>


@section('scripts')
    <script src="{{ asset('js/live-price.js?ref='.rand(1111,9999)) }}"></script>
    <script>
        $(document).ready(function(){
            $('#myTab li:first-child a').click();
        });
    </script>
@endsection