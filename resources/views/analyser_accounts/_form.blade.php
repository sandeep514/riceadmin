@php
    $business = $businessDetails ?? (isset($model) && $model->user ? $model->user->getWebBusinessDetails : null);
    $biz = function ($key, $default = null) use ($business) {
        return old('business.'.$key, $business ? $business->{$key} : $default);
    };
    $categoryOptions = ['' => 'Select category'] + ($categories ?? []);
    $stateOptions = ['' => 'Select state'] + ($states ?? []);
@endphp
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
        <div class="form-group col-md-6 @error('password') has-error @enderror">
            {!! Form::label('password', isset($model) ? 'Password (leave blank to keep current)' : 'Password*') !!}
            {!! Form::password('password', ['class'=>'form-control', 'autocomplete'=>'new-password']) !!}
            @error('password')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('password_confirmation') has-error @enderror">
            {!! Form::label('password_confirmation', isset($model) ? 'Confirm Password (if changing)' : 'Confirm Password*') !!}
            {!! Form::password('password_confirmation', ['class'=>'form-control', 'autocomplete'=>'new-password']) !!}
            @error('password_confirmation')
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
                    {!! Form::checkbox('send_mail', 1, !isset($model)) !!} Send mail to client with login URL, email and password
                </label>
            </div>
            <p class="help-block">Client can log in with email and password. OTP login remains available as a fallback.</p>
        </div>
    </div>

    <hr>
    <h4 style="margin-top: 0;">Business Details (Web Portal)</h4>
    <div class="row">
        <div class="form-group col-md-6 @error('business.company_name') has-error @enderror">
            {!! Form::label('business[company_name]','Company Name') !!}
            {!! Form::text('business[company_name]', $biz('company_name'), ['class'=>'form-control']) !!}
            @error('business.company_name')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.product') has-error @enderror">
            {!! Form::label('business[product]','Product') !!}
            {!! Form::text('business[product]', $biz('product'), ['class'=>'form-control']) !!}
            @error('business.product')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.contactPerson') has-error @enderror">
            {!! Form::label('business[contactPerson]','Business Contact Person') !!}
            {!! Form::text('business[contactPerson]', $biz('contactPerson'), ['class'=>'form-control']) !!}
            @error('business.contactPerson')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.contactMobile') has-error @enderror">
            {!! Form::label('business[contactMobile]','Business Contact Mobile') !!}
            {!! Form::text('business[contactMobile]', $biz('contactMobile'), ['class'=>'form-control']) !!}
            @error('business.contactMobile')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.designation') has-error @enderror">
            {!! Form::label('business[designation]','Designation') !!}
            {!! Form::text('business[designation]', $biz('designation'), ['class'=>'form-control']) !!}
            @error('business.designation')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.registered_email') has-error @enderror">
            {!! Form::label('business[registered_email]','Registered Email') !!}
            {!! Form::text('business[registered_email]', $biz('registered_email'), ['class'=>'form-control']) !!}
            @error('business.registered_email')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.phone') has-error @enderror">
            {!! Form::label('business[phone]','Phone') !!}
            {!! Form::text('business[phone]', $biz('phone'), ['class'=>'form-control']) !!}
            @error('business.phone')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.selected_category') has-error @enderror">
            {!! Form::label('business[selected_category]','Selected Category') !!}
            {!! Form::select('business[selected_category]', $categoryOptions, $biz('selected_category'), ['class'=>'form-control']) !!}
            @error('business.selected_category')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-12 @error('business.address') has-error @enderror">
            {!! Form::label('business[address]','Address') !!}
            {!! Form::textarea('business[address]', $biz('address'), ['class'=>'form-control','rows'=>2]) !!}
            @error('business.address')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.locality') has-error @enderror">
            {!! Form::label('business[locality]','Locality') !!}
            {!! Form::text('business[locality]', $biz('locality'), ['class'=>'form-control']) !!}
            @error('business.locality')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.landmark') has-error @enderror">
            {!! Form::label('business[landmark]','Landmark') !!}
            {!! Form::text('business[landmark]', $biz('landmark'), ['class'=>'form-control']) !!}
            @error('business.landmark')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.state') has-error @enderror">
            {!! Form::label('business[state]','State') !!}
            {!! Form::select('business[state]', $stateOptions, $biz('state'), ['class'=>'form-control','id'=>'business_state']) !!}
            @error('business.state')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('business.city') has-error @enderror">
            {!! Form::label('business[city]','City') !!}
            <select name="business[city]" id="business_city" class="form-control">
                <option value="">Select city</option>
            </select>
            @error('business.city')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('business.status') has-error @enderror">
            {!! Form::label('business[status]','Business Status') !!}
            {!! Form::select('business[status]', [1=>'Active',0=>'Inactive'], (int) $biz('status', 1), ['class'=>'form-control']) !!}
            @error('business.status')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('business.is_sntc_recommended') has-error @enderror">
            {!! Form::label('business[is_sntc_recommended]','SNTC Recommended') !!}
            {!! Form::select('business[is_sntc_recommended]', [0=>'No',1=>'Yes'], (int) $biz('is_sntc_recommended', 0), ['class'=>'form-control']) !!}
            @error('business.is_sntc_recommended')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('business.is_active_listing') has-error @enderror">
            {!! Form::label('business[is_active_listing]','Active Listing') !!}
            {!! Form::select('business[is_active_listing]', [0=>'No',1=>'Yes'], (int) $biz('is_active_listing', 0), ['class'=>'form-control']) !!}
            @error('business.is_active_listing')
            <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
@php
    $cityRows = collect($cities ?? [])->map(function ($row) {
        return [
            'id' => (string) $row->id,
            'state_id' => (string) $row->state_id,
            'name' => $row->city_name,
        ];
    })->values();
    $selectedCity = (string) $biz('city', '');
@endphp
<script>
window.__analyserAccountForm = {
    cities: @json($cityRows),
    selectedCity: @json($selectedCity)
};
</script>
