<div class="box-body">
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Update Contact</label>
                <div class="group-content">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('phone','Contact Phone') !!}
                                {!! Form::text('phone', $contact->phone ,['class'=>'form-control', 'required' => 'required' , 'maxlength' => 10 , 'minlength' => 10]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('email','Contact Email') !!}
                                {!! Form::email('email', $contact->email ,['class'=>'form-control', 'required' => 'required']) !!}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>