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
                    <li class="active"><a href="#ph" data-toggle="tab">Punjab - Haryana</a></li>
                    <li><a href="#kb" data-toggle="tab">Rajasthan</a></li>
                    <li><a href="#mp" data-toggle="tab">Madhya Pradesh</a></li>
                    
                    <li><a href="#maha" data-toggle="tab">Maharashtra</a></li>
                    <li><a href="#karn" data-toggle="tab">Karnataka</a></li>
                    <li><a href="#anpr" data-toggle="tab">Andhra pradesh</a></li>
                    <li><a href="#webe" data-toggle="tab">West bengal</a></li>
                    <li><a href="#utpr" data-toggle="tab">Uttar pradesh</a></li>
                    <li><a href="#guj" data-toggle="tab">Gujarat</a></li>
                </ul>
                <div id="myTabContent" class="tab-content" >

                    <div class="tab-pane fade in active" id="ph">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','punjab_haryana')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                    
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[punjab_haryana]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[punjab_haryana]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[punjab_haryana]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="kb">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','rajasthan')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[rajasthan]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[rajasthan]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[rajasthan]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mp">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','madhya_pradesh')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[madhya_pradesh]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[madhya_pradesh]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[madhya_pradesh]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="guj">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','gujrat')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[gujrat]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[gujrat]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[gujrat]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="maha">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','maharashtra')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[maharashtra]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[maharashtra]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[maharashtra]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="karn">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','karnataka')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[karnataka]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[karnataka]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[karnataka]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="anpr">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','andhra_pradesh')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[andhra_pradesh]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[andhra_pradesh]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[andhra_pradesh]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="webe">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','west_bengal')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[west_bengal]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[west_bengal]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[west_bengal]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="utpr">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','uttar_pradesh')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[uttar_pradesh]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[uttar_pradesh]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[uttar_pradesh]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

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
        @else
            <div class="responsiveTabs nonbasmatitabs">
                <ul id="myTab" class="nav nav-tabs" style="margin-bottom: 15px;">
                    <li class="active"><a href="#ph" data-toggle="tab">Punjab - Haryana</a></li>
                    {{-- <li><a href="#kb" data-toggle="tab">Kota Bundi</a></li> --}}
                    <li><a href="#mp" data-toggle="tab">Madhya Pradesh</a></li>
                    <li><a href="#mst" data-toggle="tab">Maharashtra</a></li>
                    <li><a href="#ktk" data-toggle="tab">Karnataka</a></li>
                    <li><a href="#ap" data-toggle="tab">Andhra Pradesh</a></li>
                    <li><a href="#wb" data-toggle="tab">West Bengal</a></li>
                    <!-- <li><a href="#kerl" data-toggle="tab">Keral</a></li> -->
                    <li><a href="#utp" data-toggle="tab">Uttar Pradesh</a></li>
                    <li><a href="#rajasthan" data-toggle="tab">Rajasthan</a></li>
                    <!-- <li><a href="#kolkata" data-toggle="tab">Kolkata</a></li> -->
                    <li><a href="#gujarat" data-toggle="tab">Gujarat</a></li>
                </ul>
                <div id="myTabContent" class="tab-content " >
                    <div class="tab-pane fade in active" id="ph">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','punjab_haryana')->first();
                                                        
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[punjab_haryana]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[punjab_haryana]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[punjab_haryana]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mp">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','madhya_pradesh')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[madhya_pradesh]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[madhya_pradesh]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[madhya_pradesh]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mst">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','maharashtra')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[maharashtra]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[maharashtra]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[maharashtra]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ktk">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','karnataka')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }    
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[karnataka]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[karnataka]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[karnataka]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ap">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','andhra_pradesh')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[andhra_pradesh]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[andhra_pradesh]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[andhra_pradesh]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="wb">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','west_bengal')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[west_bengal]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[west_bengal]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[west_bengal]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="tab-pane fade" id="kerl">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','west_bengal')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[west_bengal]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[west_bengal]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[west_bengal]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="tab-pane fade" id="utp">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','uttar_pradesh')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[uttar_pradesh]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[uttar_pradesh]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[uttar_pradesh]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="rajasthan">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','rajasthan')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[rajasthan]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[rajasthan]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[rajasthan]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="kolkata">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','kolkata')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[kolkata]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[kolkata]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[kolkata]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="gujarat">
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
                                                        $details = $lastPrices->where('form',$form->id)->where('state','gujarat')->first();
                                                        if($details != null){
                                                            $min = $details->min_price;
                                                            $max = $details->max_price;
                                                            $upDown = $details->up_down;
                                                        }
                                                    }
                                                @endphp
                                                @if( $form->form_name != 'Water Color Sella Rice ' )
                                                    <tr>
                                                        <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                        <td>
                                                            {!! Form::text('min[gujarat]['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('max[gujarat]['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::select('up_down[gujarat]['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                        </td>
                                                    </tr>
                                                @endif

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
        @endif
    @endif
</div>


@section('scripts')
    <script src="{{ asset('js/live-price.js?ref='.rand(1111,9999)) }}"></script>
@endsection