<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('role_name') has-error @enderror">
            {!! Form::label('role_name','Role Name*') !!}
            {!! Form::text('role_name',null,['class'=>'form-control','id'=>'category']) !!}
            @error('role_name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('type') has-error @enderror">
            {!! Form::label('type','Role Type*') !!}
            {!! Form::select('type',['web'=>'Web','app'=>'App'],null,['class'=>'form-control','id'=>'type','placeholder'=>'Select Type']) !!}
            @error('type')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
            <small class="help-block">Web roles appear in Web Access, Web Plans &amp; Role Category Map.</small>
        </div>
    </div>
</div>
