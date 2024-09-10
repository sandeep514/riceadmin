<div class="box-body">
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Create Plan</label>
                <div class="group-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('plan_name','Plan Name') !!}
                                {!! Form::text('plan_name', null ,['class'=>'form-control', 'required' => 'required']) !!}
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <h3 style="font-size: 18px;margin-left: 13px;font-weight: 600;">Chart Intervals</h3>
                                    @foreach( $ChartInterval as $k => $v )
                                        @if($k > 0)
                                        <div class="col-md-2">
                                            {!! Form::checkbox('chartint['.$v->id.']', $v->id ,['class'=>'form-control']) !!}
                                            {!! Form::label( 'chartint',$v->name ) !!}
                                        </div>
                                        @endif
                                    @endforeach   
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="box-body">
                        <div class="row margin-top-10">
                            <div class="col-md-12">
                                <div class="group-panel">
                                    <label class="group-title">Sub Plan</label>
                                    <div class="group-content">
                                        
                                        <div class="row">
                                            <!--<h3 style="font-size: 18px;margin-left: 13px;font-weight: 600;">Sub Plan</h3>-->
                                            @foreach( $SubPlan as $k => $v )
                                                <div class="col-md-4">
                                                    <!--{!! Form::checkbox('subplan[]',$v->id,['checked' => 'false' , 'class'=>'form-control']) !!}-->
                                                    {!! Form::label('subplan[]', $v->name) !!}
                                                    
                                                    {!! Form::label('subplan['.$v->id.']','Plan Price') !!}
                                                    {!! Form::text('subplan['.$v->id.']', null ,['placeholder' => 'Rs' ,'class'=>'form-control']) !!}
                                                    
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                    
                    
                    
                  
                    
                </div>
            </div>
        </div>
    </div>
</div>