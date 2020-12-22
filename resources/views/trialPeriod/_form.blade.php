<div class="box-body">
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Update Trial Period</label>
                <div class="group-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('plan_name','Add Months') !!}
                                {!! Form::number('month', ($trialPeriod) ? $trialPeriod->month : '' ,['class'=>'form-control', 'required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>