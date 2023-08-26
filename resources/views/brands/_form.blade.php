<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('brand_name') has-error @enderror">
            {!! Form::label('brandName','Brand Name*') !!}
            {!! Form::text('brand_name','',['class'=>'form-control','id'=>'brandName']) !!}
            @error('brand_name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('brand_logo') has-error @enderror">
            {!! Form::label('brand_logo','Brand Logo*') !!}
            {!! Form::file('brand_logo',['class'=>'form-control','id'=>'brandLogo']) !!}
            @error('brand_logo')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
