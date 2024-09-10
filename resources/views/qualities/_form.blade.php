<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('name') has-error @enderror">
            {!! Form::label('name','Name*') !!}
            {!! Form::select('name',\App\RiceName::qualityNames(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Name']) !!}
            @error('name')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('category') has-error @enderror">
            {!! Form::label('category','Form*') !!}
            {!! Form::select('category',\App\RiceForm::riceForms(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Form']) !!}
            @error('category')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('type') has-error @enderror">
            {!! Form::label('type','Type*') !!}
            {!! Form::select('type',\App\RiceType::riceTypes(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Type']) !!}
            @error('type')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

    </div>
</div>
