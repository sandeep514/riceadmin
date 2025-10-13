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
                {!! Form::label('message','Message*') !!}
                {!! Form::text('message',null,['class'=>'form-control','id'=>'message','placeholder' => 'Message']) !!}
                @error('message')
                    <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
                @enderror
            </div>
            <div>
                {!! Form::label('designation','Designation*') !!}
                {!! Form::text('designation',null,['class'=>'form-control','id'=>'designation','placeholder' => 'designation']) !!}
                @error('designation')
                    <span class="help-block text-danger" role="alert">
                        {{ $designation }}
                    </span>
                @enderror
            </div>
            <div>
                {!! Form::label('date','Added at*') !!}
                {!! Form::date('date',null,['class'=>'form-control','id'=>'date','placeholder' => 'Added at']) !!}
                @error('date')
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
