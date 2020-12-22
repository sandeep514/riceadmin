<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('name') has-error @enderror">
            {!! Form::label('name','Name*') !!}
            {!! Form::text('name',null,['class'=>'form-control','id'=>'category']) !!}
            @error('name')
            <span class="help-block text-danger" role="alert">
                {{ $message }}
            </span>
            @enderror
        </div>
        @php
            $optionalText = '';
            if(request()->role == 3){
                $optionalText = ' (optional)';
            }else{
                $optionalText = '*';
            }
        @endphp
        <div class="form-group col-md-6 @error('email') has-error @enderror">
            {!! Form::label('email','Email'.$optionalText) !!}
            {!! Form::text('email',null,['class'=>'form-control','id'=>'category']) !!}
            @error('email')
            <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
            @enderror
        </div>


        @if(in_array(request()->role,[4,5]))
            <div class="form-group col-md-6 @error('company') has-error @enderror">
                {!! Form::label('company','Company Name*') !!}
                {!! Form::text('company',null,['class'=>'form-control','id'=>'company']) !!}
                @error('company')
                <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
                @enderror
            </div>
            <div class="form-group col-md-6 @error('contact_person') has-error @enderror">
                {!! Form::label('contact_person','Contact Person*') !!}
                {!! Form::text('contact_person',null,['class'=>'form-control','id'=>'contact_person']) !!}
                @error('contact_person')
                    <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        @endif

        <div class="form-group col-md-6 @error('password') has-error @enderror">
            {!! Form::label('password','Password*') !!}
            {!! Form::password('password',['class'=>'form-control','id'=>'category']) !!}
            @error('password')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('phone') has-error @enderror">
            {!! Form::label('phone','Phone*') !!}
            {!! Form::text('phone',null,['class'=>'form-control','id'=>'category']) !!}
            @error('phone')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-6 @error('mobile') has-error @enderror">
            {!! Form::label('mobile','Mobile*') !!}
            {!! Form::text('mobile',null,['class'=>'form-control','id'=>'category']) !!}
            @error('mobile')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
{{--        @if(in_array(request()->role, [5]))--}}
{{--            <div class="form-group col-md-6 @error('gst_no') has-error @enderror">--}}
{{--                {!! Form::label('gst_no','GST No*') !!}--}}
{{--                {!! Form::text('gst_no',null,['class'=>'form-control','id'=>'category']) !!}--}}
{{--                @error('gst_no')--}}
{{--                    <span class="help-block text-danger" role="alert">--}}
{{--                        {{ $message }}--}}
{{--                    </span>--}}
{{--                @enderror--}}
{{--            </div>--}}
{{--        @endif--}}
        @if(request()->role == 3)
            @php
                $selectedZone = null;
                $selectedDesignation = null;
                if(isset($model)){
                    $selectedZone = $model->field_runner_rel->zone;
                    $selectedDesignation = $model->field_runner_rel->designation;
                }
            @endphp
            <div class="form-group col-md-6 @error('zone') has-error @enderror">
                {!! Form::label('zone','Zone*') !!}
                {!! Form::select('zone',\App\CityZone::zones(),$selectedZone,['class'=>'form-control','id'=>'category','placeholder'=>'Select Zone']) !!}
                @error('zone')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                    </span>
                @enderror
            </div>
            <div class="form-group col-md-6 @error('designation') has-error @enderror">
                {!! Form::label('designation','Designation*') !!}
                {!! Form::select('designation',\App\Designation::designationsList(),$selectedDesignation,['class'=>'form-control','id'=>'designation','placeholder'=>'Select Designation']) !!}
                @error('designation')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                    </span>
                @enderror
            </div>
        @endif
        @if(in_array(request()->role, [4,5]))
            <div class="form-group col-md-6 @error('state') has-error @enderror">
                {!! Form::label('state','State*') !!}
                {!! Form::select('state',\App\State::statesList(),null,['class'=>'form-control','id'=>'state','placeholder'=>'Select State']) !!}
                @error('city')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group col-md-6 @error('city') has-error @enderror">
                {!! Form::label('city','City*') !!} <a href="javascript:void(0)" style="font-size: 20px; margin-left: 10px; margin-top: 5px;" data-toggle="modal" data-target="#modal-default"><b>+</b></a>
                {!! Form::select('city',\App\City::userCities(),null,['class'=>'form-control','id'=>'user_city','placeholder'=>'Select City']) !!}
                @error('city')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                    </span>
                @enderror
            </div>
        @endif
{{--        @if(in_array(request()->role, [5]))--}}
{{--            <div class="form-group col-md-6 @error('address') has-error @enderror">--}}
{{--                {!! Form::label('address','Address') !!}--}}
{{--                {!! Form::textarea('address',null,['class'=>'form-control','id'=>'category']) !!}--}}
{{--                @error('address')--}}
{{--                    <span class="help-block text-danger" role="alert">--}}
{{--                        {{ $message }}--}}
{{--                    </span>--}}
{{--                @enderror--}}
{{--            </div>--}}
{{--        @endif--}}

        @if(in_array(request()->role,[4,5]))
            <div class="form-group col-md-12">
                <a href="javascript:void(0)" class="btn btn-info pull-right margin-r-5 add_more_row">+</a>
                <div class="email-ids">
                    @if(isset($model))
                        @foreach($model->email_ids as $person => $emailid)
                            <div class="row email-row">
                                <div class="col-md-4 form-group">
                                    {!! Form::label('email_of','Email of*') !!}
                                    {!! Form::text('email_of[]',$person,['class'=>'form-control','placeholder'=>'Enter email of person']) !!}
                                </div>
                                <div class="col-md-4 form-group">
                                    {!! Form::label('email_id','Email Address*') !!}
                                    {!! Form::text('email_id[]',$emailid,['class'=>'form-control','placeholder'=>'Enter email address']) !!}
                                </div>
                                <div class="col-md-1 remove-email">
                                    <a href="javascript:void(0)" class="remove-email-row"><i class="fa fa-trash-o"></i></a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row email-row">
                            <div class="col-md-4 form-group">
                                {!! Form::label('email_of','Email of*') !!}
                                {!! Form::text('email_of[]',null,['class'=>'form-control','placeholder'=>'Enter email of person']) !!}
                            </div>
                            <div class="col-md-4 form-group">
                                {!! Form::label('email_id','Email Address*') !!}
                                {!! Form::text('email_id[]',null,['class'=>'form-control','placeholder'=>'Enter email address']) !!}
                            </div>
                            <div class="col-md-1 remove-email">
                                <a href="javascript:void(0)" class="remove-email-row"><i class="fa fa-trash-o"></i></a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="modal-default">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create City</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label>Enter City</label>
                        {!! Form::text('modal_city',null,['class'=>'form-control new_city']) !!}
                        <span class="help-block text-danger city_error" role="alert" style="color: red;">

                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary save_city">Save City</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@section('scripts')
    <script src="{{ asset('js/custom.js') }}" type="text/javascript"></script>
@endsection
