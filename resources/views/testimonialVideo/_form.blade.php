<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('title') has-error @enderror">
            <div>
                {!! Form::label('title','Title*') !!}
                {!! Form::text('title',null,['class'=>'form-control','id'=>'title','placeholder' => 'Title']) !!}
                @error('title')
                    <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
                @enderror
            </div>
            <div>
                {!! Form::label('file','Profile*') !!}
                {!! Form::file('file',null,['class'=>'form-control','id'=>'file','placeholder' => 'profile']) !!}
                @error('file')
                    <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>
