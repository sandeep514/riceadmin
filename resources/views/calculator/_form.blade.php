<?php
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'https://api.exchangerate.host/convert?from=USD&to=INR');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $result = json_decode($response);
    curl_close($ch); // Close the connection

    // $dollarRate = $result->result;
    $dollarRate = $dollarRate;
?>

<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            {!! Form::label('Rice','Rice*') !!}
            <select class="form-control" name="riceName" id="riceName">
                @foreach( $riceName as $k => $v )
                    <option value="{{ $v->id }}" >{{ $v->quality }} {{ $v->quality_name }} ({{ $v->quality_type }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-3 ">
            {!! Form::label('rice','Cost of Rice Ex Mill Min price*') !!}
            {!! Form::text('ricemin',null,['class'=>'form-control','id'=>'ricemin' , 'required']) !!}
        </div>
        <div class="form-group col-md-3 ">
            {!! Form::label('rice','Cost of Rice Ex Mill Max price*') !!}
            {!! Form::text('ricemax',null,['class'=>'form-control','id'=>'ricemax' , 'required']) !!}
        </div>
        <div class="form-group col-md-3 ">
            {!! Form::label('rice','Transport Min price *') !!}
            {!! Form::text('portmin',null,['class'=>'form-control','id'=>'transportmin' , 'required']) !!}
        </div>
        <div class="form-group col-md-3 ">
            {!! Form::label('rice','Transport Max price *') !!}
            {!! Form::text('portmax',null,['class'=>'form-control','id'=>'transportmax' , 'required']) !!}
        </div>
        <div class="form-group col-md-3 ">
            {!! Form::label('bag','Bag Cost including Sortexing & packing labour*') !!}
            {!! Form::text('bag',$defaultValue['bagcost'],['class'=>'form-control','id'=>'category']) !!}
        </div>


        <div class="form-group col-md-3 ">
            {!! Form::label('charges','All Local charges( CFS Handling, B/L, THC ), Finance cost*') !!}
            {!! Form::text('charges',$defaultValue['localcharges'],['class'=>'form-control','id'=>'charges']) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('dollar',"Today's Dollar rate *") !!}
            {!! Form::text('dollar',$dollarRate,['class'=>'form-control','id'=>'dollarrate']) !!}
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('percentage','Supplier Margin+SNTC Service charge (1 - 20%)*') !!}
            <select class="form-control" name="percentage" id="percentage">
                <option value="0" selected >0</option>
                <option value="1" >1</option>
                <option value="2" >2</option>
                <option value="3" >3</option>
                <option value="4" >4</option>
                <option value="5" >5</option>
                <option value="6" >6</option>
                <option value="7" >7</option>
                <option value="8" >8</option>
                <option value="9" >9</option>
                <option value="10" >10</option>
                <option value="11" >11</option>
                <option value="12" >12</option>
                <option value="13" >13</option>
                <option value="14" >14</option>
                <option value="15" >15</option>
                <option value="16" >16</option>
                <option value="17" >17</option>
                <option value="18" >18</option>
                <option value="19" >19</option>
                <option value="20" >20</option>
            </select>
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('color_status','Color') !!}
            <select class="form-control" name="color_status" id="color_status">
                <option value="1" >Green</option>
                <option value="2" >Red</option>
                <option value="3" selected >Black</option>                
            </select>
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('total','Total: (in ₹) ') !!}
            <span id="total"></span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('exchangeRate','After exchange rate: (in $)  ') !!}
            <span id="exchangeRate"></span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('fob','FOB Prices: ') !!}
            <span id="fob"></span>
        </div>

        

    </div>
</div>
