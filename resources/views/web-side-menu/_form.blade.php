<div class="box-body">
    <div class="row">
        <div class="form-group col-md-4 @error('title') has-error @enderror">
            {!! Form::label('title','Title*') !!}
            {!! Form::text('title',isset($model) ? $model->title : null,['class'=>'form-control','id'=>'title','placeholder'=>'Enter menu title']) !!}
            @error('title')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('sub_title') has-error @enderror">
            {!! Form::label('sub_title','Sub Title') !!}
            {!! Form::text('sub_title',isset($model) ? $model->sub_title : null,['class'=>'form-control','id'=>'sub_title','placeholder'=>'Enter sub title']) !!}
            @error('sub_title')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('slug') has-error @enderror">
            {!! Form::label('slug','Slug') !!}
            @if(isset($model))
                {!! Form::text('slug', $model->slug, ['class'=>'form-control','id'=>'slug','readonly'=>'readonly', 'style'=>'background-color: #f5f5f5;']) !!}
                <small class="help-block">Slug is auto-generated and cannot be edited.</small>
            @else
                {!! Form::text('slug', null, ['class'=>'form-control','id'=>'slug','readonly'=>'readonly', 'style'=>'background-color: #f5f5f5;', 'placeholder'=>'Auto-generated from title']) !!}
                <small class="help-block">Slug will be auto-generated from title when saved.</small>
            @endif
            @error('slug')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6 @error('create_url') has-error @enderror">
            {!! Form::label('create_url','Create URL (Optional)') !!}
            {!! Form::text('create_url',isset($model) ? $model->create_url : null,['class'=>'form-control','id'=>'create_url','placeholder'=>'Enter create URL (optional)']) !!}
            @error('create_url')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('read_url') has-error @enderror">
            {!! Form::label('read_url','Read URL (Optional)') !!}
            {!! Form::text('read_url',isset($model) ? $model->read_url : null,['class'=>'form-control','id'=>'read_url','placeholder'=>'Enter read URL (optional)']) !!}
            @error('read_url')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6 @error('update_url') has-error @enderror">
            {!! Form::label('update_url','Update URL (Optional)') !!}
            {!! Form::text('update_url',isset($model) ? $model->update_url : null,['class'=>'form-control','id'=>'update_url','placeholder'=>'Enter update URL (optional)']) !!}
            @error('update_url')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('delete_url') has-error @enderror">
            {!! Form::label('delete_url','Delete URL (Optional)') !!}
            {!! Form::text('delete_url',isset($model) ? $model->delete_url : null,['class'=>'form-control','id'=>'delete_url','placeholder'=>'Enter delete URL (optional)']) !!}
            @error('delete_url')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6 @error('status') has-error @enderror">
            {!! Form::label('status','Status*') !!}
            {!! Form::select('status',[1=>'Active',0=>'Inactive'],isset($model) ? $model->status : 1,['class'=>'form-control','id'=>'status']) !!}
            @error('status')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('sort_order') has-error @enderror">
            {!! Form::label('sort_order','Sort Order') !!}
            {!! Form::number('sort_order',isset($model) ? $model->sort_order : null,['class'=>'form-control','id'=>'sort_order','placeholder'=>'Enter sort order (lower numbers appear first)','min'=>'0']) !!}
            @error('sort_order')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
            <small class="help-block">Lower numbers appear first in the menu. Leave empty to add at the end.</small>
        </div>
    </div>
</div>

