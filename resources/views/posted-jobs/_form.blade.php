<div class="box-body">
    <div class="row">
        <div class="form-group col-md-12 @error('title') has-error @enderror">
            {!! Form::label('title', 'Title*') !!}
            {!! Form::text('title', null, ['class' => 'form-control', 'id' => 'title', 'maxlength' => 500]) !!}
            @error('title')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-12 @error('description') has-error @enderror">
            {!! Form::label('description', 'Description*') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 6, 'id' => 'description']) !!}
            @error('description')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-12 @error('job_role') has-error @enderror">
            {!! Form::label('job_role', 'Role of job') !!}
            {!! Form::text('job_role', null, ['class' => 'form-control', 'id' => 'job_role', 'maxlength' => 500, 'placeholder' => 'e.g. Senior trader, Operations executive']) !!}
            @error('job_role')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('location') has-error @enderror">
            {!! Form::label('location', 'Location*') !!}
            {!! Form::text('location', null, ['class' => 'form-control', 'id' => 'location', 'maxlength' => 255, 'placeholder' => 'City / office']) !!}
            @error('location')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('employment_type') has-error @enderror">
            {!! Form::label('employment_type', 'Type*') !!}
            {!! Form::select('employment_type', ['' => '— Select —'] + \App\PostedJob::employmentTypeOptions(), null, ['class' => 'form-control', 'id' => 'employment_type']) !!}
            @error('employment_type')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('last_date_apply') has-error @enderror">
            {!! Form::label('last_date_apply', 'Last date to apply*') !!}
            {!! Form::date('last_date_apply', old('last_date_apply', isset($job) && $job->last_date_apply ? $job->last_date_apply->format('Y-m-d') : null), ['class' => 'form-control', 'id' => 'last_date_apply']) !!}
            @error('last_date_apply')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('number_of_positions') has-error @enderror">
            {!! Form::label('number_of_positions', 'Number of positions*') !!}
            {!! Form::number('number_of_positions', null, ['class' => 'form-control', 'id' => 'number_of_positions', 'min' => 1, 'max' => 99999]) !!}
            @error('number_of_positions')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
