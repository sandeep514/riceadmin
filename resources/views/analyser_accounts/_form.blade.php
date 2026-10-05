<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('name') has-error @enderror">
            {!! Form::label('name','Name*') !!}
            {!! Form::text('name', isset($model) && $model->user ? $model->user->name : null, ['class'=>'form-control']) !!}
            @error('name')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('email') has-error @enderror">
            {!! Form::label('email','Email*') !!}
            {!! Form::text('email', isset($model) && $model->user ? $model->user->email : null, ['class'=>'form-control']) !!}
            @error('email')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('contact_person_name') has-error @enderror">
            {!! Form::label('contact_person_name','Contact Person Name*') !!}
            {!! Form::text('contact_person_name', isset($model) ? $model->contact_person_name : null, ['class'=>'form-control']) !!}
            @error('contact_person_name')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('contact_mobile') has-error @enderror">
            {!! Form::label('contact_mobile','Contact Mobile*') !!}
            {!! Form::text('contact_mobile', isset($model) ? $model->contact_mobile : null, ['class'=>'form-control']) !!}
            @error('contact_mobile')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('start_date') has-error @enderror">
            {!! Form::label('start_date','Start Date*') !!}
            {!! Form::date('start_date', isset($model) && $model->start_date ? $model->start_date->format('Y-m-d') : null, ['class'=>'form-control']) !!}
            @error('start_date')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('end_date') has-error @enderror">
            {!! Form::label('end_date','End Date*') !!}
            {!! Form::date('end_date', isset($model) && $model->end_date ? $model->end_date->format('Y-m-d') : null, ['class'=>'form-control']) !!}
            @error('end_date')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('received_amount') has-error @enderror">
            {!! Form::label('received_amount','Received Amount (optional)') !!}
            {!! Form::number('received_amount', isset($model) ? $model->received_amount : null, ['class'=>'form-control','step'=>'0.01','min'=>'0']) !!}
            @error('received_amount')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('download_limit') has-error @enderror">
            {!! Form::label('download_limit','Number of Download Access (optional)') !!}
            {!! Form::number('download_limit', isset($model) ? $model->download_limit : null, ['class'=>'form-control','step'=>'1','min'=>'1','placeholder'=>'Leave blank for unlimited']) !!}
            @error('download_limit')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('has_historical_access') has-error @enderror">
            {!! Form::label('has_historical_access','Has Access of Historical Data*') !!}
            {!! Form::select('has_historical_access', [1=>'Yes',0=>'No'], isset($model) ? (int) $model->has_historical_access : 1, ['class'=>'form-control']) !!}
            @error('has_historical_access')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('has_today_access') has-error @enderror">
            {!! Form::label('has_today_access','Has Access of Today Data*') !!}
            {!! Form::select('has_today_access', [1=>'Yes',0=>'No'], isset($model) ? (int) $model->has_today_access : 1, ['class'=>'form-control']) !!}
            @error('has_today_access')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-12">
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('send_mail', 1, !isset($model)) !!} Send mail to client with login URL and email
                </label>
            </div>
            <p class="help-block">No password is set. The client logs in with the OTP sent on mail.</p>
        </div>
    </div>
</div>
