@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Dashboard
                <small>Control panel</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard </li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>150</h3>

                            <p>New Orders</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="javascript:void(0)" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3>53<sup style="font-size: 20px">%</sup></h3>

                            <p>Bounce Rate</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="javascript:void(0)" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>44</h3>

                            <p>User Registrations</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="javascript:void(0)" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3>65</h3>
                            <p>Unique Visitors</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>
                        <a href="javascript:void(0)" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <div class="row">
                <div class="col-md-6">
                        <div class="form-group col-md-4">
                            {!! Form::label('rice1','Quality Cost of Rice 1*') !!}
                            {!! Form::text('rice1',1200,['class'=>'form-control','id'=>'rice1cost' , 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice1','Percentage of Rice 1*') !!}
                            {!! Form::text('rice1',null,['class'=>'form-control','id'=>'rice1percentage' , 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice1','Cost of Rice1') !!}
                            {!! Form::text('rice1',null,['class'=>'form-control','id'=>'rice1' , 'disabled' => true]) !!}
                        </div>


                        <div class="form-group col-md-4">
                            {!! Form::label('rice2','Quality Cost of Rice 2') !!}
                            {!! Form::text('rice2',null,['class'=>'form-control','id'=>'rice2cost' , 'disabled' => false]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice2','Quality Cost of Rice 2') !!}
                            {!! Form::text('rice2',null,['class'=>'form-control','id'=>'rice2percentage' , 'disabled' => false]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice2','Quality Cost of Rice 2') !!}
                            {!! Form::text('rice2',null,['class'=>'form-control','id'=>'rice2' , 'disabled' => true]) !!}
                        </div>



                        <div class="form-group col-md-4">
                            {!! Form::label('rice3','Quality Cost of Rice 3') !!}
                            {!! Form::text('rice3',null,['class'=>'form-control','id'=>'rice3cost' , 'disabled' => false]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice3','Quality Cost of Rice 3') !!}
                            {!! Form::text('rice3',null,['class'=>'form-control','id'=>'rice3percentage' , 'disabled' => false]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice3','Quality Cost of Rice 3') !!}
                            {!! Form::text('rice3',null,['class'=>'form-control','id'=>'rice3' , 'disabled' => true]) !!}
                        </div>


                        <div class="form-group col-md-4">
                            {!! Form::label('rice4','Quality Cost of Rice 4') !!}
                            {!! Form::text('rice4',null,['class'=>'form-control','id'=>'rice4cost' , 'disabled' => false]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice4','Quality Cost of Rice 4') !!}
                            {!! Form::text('rice4',null,['class'=>'form-control','id'=>'rice4percentage' , 'disabled' => false]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('rice4','Quality Cost of Rice 4') !!}
                            {!! Form::text('rice4',null,['class'=>'form-control','id'=>'rice4' , 'disabled' => true]) !!}
                        </div>


                        <div class="form-group col-md-6">
                            {!! Form::label('processingCharge','Processing Charges') !!}
                            {!! Form::text('processingCharge',null,['class'=>'form-control','id'=>'processingCharge']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            <label style="width: 100%">
                                {!! Form::label('processingCharge','Bag Cost') !!}
                                <select class="form-control" name="50kgbagcost" style="width: 100%;" id="50kgbagcost">
                                    @foreach( $defaultMaster as $k => $v )
                                        <option value="{{ $v->id }}" bag-cost="{{ $v->bag_cost }}">{{ $v->bag_size }} ({{$v->bag_type}} - {{ ($v->applied_for == 1)? 'Basmati' : 'Non-Basmati' }})</option>
                                    @endforeach
                                </select>
                                <span>PMT: ₹<span class="bagCostWithDollarRate">{{ $defaultMaster[0]['bag_cost'] }}</span></span>
                            </label>
                        </div>
                       
                        <div class="form-group col-md-6">
                            {!! Form::label('domesticTransportMundra','Domestic Transport Upto Mundra') !!}
                            {!! Form::text('domesticTransportMundra','',['class'=>'form-control','id'=>'domesticTransportMundra']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            <span class="localCharges">Local Charges: ₹<span class="localChargesValue">{{$defaultvalue->localcharges}}</span></span>
                            <br />
                           
                            <span class="financecost">Finance Cost: ₹<span class="financecostvalue">{{$defaultvalue->financecost}}</span></span>

                        </div>
                        <div class="form-group col-md-6" style="border: 2px solid grey">
                            <span>Total: ₹<span class="totalValue">0</span> </span>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('currenctRate',"Today's Dollar Rate") !!}
                            {!! Form::text('currenctRate',$defaultvalue->dollarvalue,['class'=>'form-control','id'=>'currenctRate']) !!}
                        </div>              

                        <div class="form-group col-md-6">
                            <label style="width: 100%">Client Percentage
                                <select class="form-control" name="margin" style="width: 100%;" id="margin">
                                    <option value="1">1%</option>
                                    <option value="2">2%</option>
                                    <option value="3">3%</option>
                                    <option value="4">4%</option>
                                    <option value="5">5%</option>
                                    <option value="6">6%</option>
                                    <option value="7">7%</option>
                                    <option value="8">8%</option>
                                    <option value="9">9%</option>
                                    <option value="10">10%</option>
                                </select>
                            </label>
                        </div>
                        
                        <div class="form-group col-md-6">
                            {!! Form::label('PMT_FOB_Price_in_dollar',"PMT FOB Price in $") !!}<span class="pmt_fob">0</span>
                        </div>                        
                        <div class="form-group col-md-6">
                            {!! Form::label('LC_Charges',"L/C Charges") !!}
                            {!! Form::text('LC_Charges',390,['class'=>'form-control','id'=>'LC_Charges']) !!}
                        </div>                        
                        <div class="form-group col-md-6">
                            {!! Form::label('ocean_freight',"Ocean Freight Per MT") !!}
                            {!! Form::text('ocean_freight',390,['class'=>'form-control','id'=>'ocean_freight']) !!}
                        </div>                        
                        <div class="form-group col-md-6">
                            {!! Form::label('third_party_inspection',"Third Party Inspection") !!}
                            {!! Form::text('third_party_inspection',390,['class'=>'form-control','id'=>'third_party_inspection']) !!}
                        </div>                        
                        <div class="form-group col-md-6">
                            {!! Form::label('legalisation',"Legalisation Charges") !!}
                            {!! Form::text('legalisation',390,['class'=>'form-control','id'=>'legalisation']) !!}
                        </div>                        
                        <div class="form-group col-md-6">
                            {!! Form::label('COC',"COC") !!}
                            {!! Form::text('COC',390,['class'=>'form-control','id'=>'COC']) !!}
                        </div>                        
                        <div class="form-group col-md-6">
                            {!! Form::label('EIA',"EIA cost ( for EU Only )") !!}
                            {!! Form::text('EIA',390,['class'=>'form-control','id'=>'EIA']) !!}
                        </div>                        
                        <div class="form-group col-md-6">
                            {!! Form::label('SNTCServiceCharge',"SNTC Service Charge( in Percentage % )") !!}
                            <select class="form-control" name="SNTCServiceCharge" style="width: 100%;" id="SNTCServiceCharge">
                                <option value="1" selected>1%</option>
                                <option value="2">2%</option>
                                <option value="3">3%</option>
                                <option value="4">4%</option>
                                <option value="5">5%</option>
                                <option value="6">6%</option>
                                <option value="7">7%</option>
                                <option value="8">8%</option>
                                <option value="9">9%</option>
                                <option value="10">10%</option>
                            </select>
                        </div>                        

                        <span>Final CIF Price: <span class="cifprice"></span></span>
                        <div class="row" style="text-align: center;">
                            <a href="#" class="btn btn-info calculateButton">Calculate</a>
                        </div>
                </div>
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function(){

                $(document).on('change' , '#50kgbagcost' , function(){
                    $('.bagCostWithDollarRate').html($('option:selected', this).attr('bag-cost'));
                });
                $(document).on('keyup' , '#rice1cost' , function(){
                    let riceCost = $(this).val();
                    $('#rice1').val(riceCost);
                });
                $(document).on('keyup' , '#rice2cost' , function(){
                    let riceCost = $(this).val();
                    $('#rice2').val(riceCost);
                });
                $(document).on('keyup' , '#rice3cost' , function(){
                    let riceCost = $(this).val();
                    $('#rice3').val(riceCost);
                });
                $(document).on('keyup' , '#rice4cost' , function(){
                    let riceCost = $(this).val();
                    $('#rice4').val(riceCost);
                });
                

                $(document).on('keyup' , '#rice1percentage' , function(){

                    var rice1Cost = $('#rice1cost').val();
                    if(rice1Cost != 0 && rice1Cost != '' && $(this).val() != '' && $(this).val() != 0){
                        $('#rice1').val(((rice1Cost * $(this).val())/100).toFixed(2));
                    }else{
                        $('#rice1').val(0);
                    }
                });
                $(document).on('keyup' , '#rice2percentage' , function(){

                    var rice2Cost = $('#rice2cost').val();
                    if(rice2Cost != 0 && rice2Cost != '' && $(this).val() != '' && $(this).val() != 0){
                        $('#rice2').val(((rice2Cost * $(this).val())/100).toFixed(2));
                    }else{
                        $('#rice2').val(0);
                    }
                });
                $(document).on('keyup' , '#rice3percentage' , function(){

                    var rice3Cost = $('#rice3cost').val();
                    if(rice3Cost != 0 && rice3Cost != '' && $(this).val() != '' && $(this).val() != 0){
                        $('#rice3').val(((rice3Cost * $(this).val())/100).toFixed(2));
                    }else{
                        $('#rice3').val(0);
                    }
                });
                $(document).on('keyup' , '#rice4percentage' , function(){

                    var rice4Cost = $('#rice4cost').val();
                    if(rice4Cost != 0 && rice4Cost != '' && $(this).val() != '' && $(this).val() != 0){
                        $('#rice4').val(((rice4Cost * $(this).val())/100).toFixed(2));
                    }else{
                        $('#rice4').val(0);
                    }
                });

                $('.calculateButton').click(function(e){
                    e.preventDefault();
                    e.stopPropagation()

                    let riceValue = '';
                    
                    let rice1 = $('#rice1').val();

                    let rice2 = $('#rice2').val();
                    let rice3 = $('#rice3').val();
                    let rice4 = $('#rice4').val();

                    let localChargesValue = $('.localChargesValue').html();
                    let financecostvalue  = $('.financecostvalue').html();

                    let processingCharge = $('#processingCharge').val();
                    let domesticTransportPrice = $('#domesticTransportMundra').val();
                    let bagCostWithDollarRateValue = $('.bagCostWithDollarRate').html(); 
                    console.log(bagCostWithDollarRateValue);

                    if( rice1 != '' ){
                        riceValue = parseFloat(rice1);
                        if( rice2 != '' ){
                            riceValue = parseFloat(rice1) + parseFloat(rice2);
                            if( rice3 != '' ){
                                riceValue = parseFloat(rice1) + parseFloat(rice2) + parseFloat(rice3);
                                if( rice4 != '' ){
                                    riceValue = parseFloat(rice1) + parseFloat(rice2) + parseFloat(rice3) + parseFloat(rice4);
                                }
                            }
                        }
                            console.log(riceValue);

                        
                        
                        if( localChargesValue.length > 0 ){
                            riceValue = riceValue + parseFloat(localChargesValue);
                            console.log(riceValue);

                        }

                        if( financecostvalue.length > 0 ){
                            riceValue = riceValue + parseFloat(financecostvalue);
                            console.log(riceValue);

                        }

                        if( processingCharge.length > 0 ){
                            riceValue = riceValue + parseFloat(processingCharge);
                            console.log(riceValue);

                        }
                        
                        if(bagCostWithDollarRateValue.length > 0){
                            riceValue = riceValue + parseFloat(bagCostWithDollarRateValue);
                            console.log(parseFloat(bagCostWithDollarRateValue));
                        }

                        if(domesticTransportPrice.length > 0) {
                            riceValue = (riceValue + parseFloat(domesticTransportPrice))
                            console.log(riceValue);

                        }


                        $('.totalValue').html(riceValue);

                        let totalAfterDollar = ( riceValue / $('#currenctRate').val() );

                        let totalAfterClientPercentage = ((totalAfterDollar * $('#margin').val() ) / 100);
                        let totalFOB = (totalAfterDollar + totalAfterClientPercentage);
                        $('.pmt_fob').html(parseFloat(totalFOB).toFixed(2));

                        let lccharges              = $('#LC_Charges').val();
                        let ocean_freight           = $('#ocean_freight').val();
                        let third_party_inspection  = $('#third_party_inspection').val();
                        let legalisation            = $('#legalisation').val();
                        let coc                     = $('#COC').val();
                        let eia                     = $('#EIA').val();
                        let sntcservicecharge       = $('#SNTCServiceCharge').val();

                        let cifPrice = parseFloat(totalFOB);
                        if(lccharges.length > 0){
                            cifPrice = (parseFloat(cifPrice) + parseFloat(lccharges));
                        }
                        if(ocean_freight.length > 0){
                            cifPrice = (parseFloat(cifPrice) + parseFloat(ocean_freight));
                        }
                        if(third_party_inspection.length > 0){
                            cifPrice = (parseFloat(cifPrice) + parseFloat(third_party_inspection));
                        }
                        if(legalisation.length > 0){
                            cifPrice = (parseFloat(cifPrice) + parseFloat(legalisation));
                        }
                        if(coc.length > 0){
                            cifPrice = (parseFloat(cifPrice) + parseFloat(coc));
                        }
                        if(eia.length > 0){
                            cifPrice = (parseFloat(cifPrice) + parseFloat(eia));
                        }

                        if(sntcservicecharge.length > 0){
                            cifPercentage = ((parseFloat(cifPrice) * sntcservicecharge) / 100);
                            cifPrice  = (parseFloat(cifPrice) + cifPercentage);
                        }

                        $('.cifprice').html((cifPrice).toFixed(2));
                    }

                    return false;                                        

                });
            });
        </script>
    @endsection
@endsection
