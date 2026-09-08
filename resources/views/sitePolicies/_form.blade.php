<div class="box-body">
    <div class="row">
        <div class="form-group col-md-4 @error('slug') has-error @enderror">
            {!! Form::label('slug', 'Type / Slug*') !!}
            @if(isset($model))
                {!! Form::text('slug', $model->slug, ['class' => 'form-control', 'readonly' => true]) !!}
                <p class="help-block">Slug cannot be changed after create.</p>
            @else
                <select name="slug_preset" id="slug_preset" class="form-control" style="margin-bottom:8px;">
                    <option value="">— Choose preset or enter custom —</option>
                    @foreach($predefined as $slug => $label)
                        <option value="{{ $slug }}"
                            {{ in_array($slug, $usedSlugs ?? [], true) ? 'disabled' : '' }}
                            {{ old('slug') === $slug ? 'selected' : '' }}>
                            {{ $label }} ({{ $slug }})
                            {{ in_array($slug, $usedSlugs ?? [], true) ? ' — already added' : '' }}
                        </option>
                    @endforeach
                    <option value="__custom__">Custom…</option>
                </select>
                {!! Form::text('slug', old('slug'), [
                    'class' => 'form-control',
                    'id' => 'slug',
                    'maxlength' => 100,
                    'placeholder' => 'e.g. terms_and_conditions',
                ]) !!}
                <p class="help-block">Lowercase letters, numbers, underscore. Use <code>terms_and_conditions</code> for welcome mail attachment.</p>
            @endif
            @error('slug')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-5 @error('title') has-error @enderror">
            {!! Form::label('title', 'Title*') !!}
            {!! Form::text('title', isset($model) ? $model->title : old('title'), [
                'class' => 'form-control',
                'id' => 'title',
                'maxlength' => 255,
                'placeholder' => 'Display title',
                'required' => true,
            ]) !!}
            @error('title')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group col-md-3 @error('status') has-error @enderror">
            {!! Form::label('status', 'Status*') !!}
            {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], isset($model) ? $model->status : old('status', 1), [
                'class' => 'form-control',
                'id' => 'status',
            ]) !!}
            @error('status')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12 @error('content') has-error @enderror">
            {!! Form::label('content', 'Content*') !!}
            {!! Form::textarea('content', isset($model) ? $model->content : old('content'), [
                'class' => 'form-control',
                'id' => 'content',
                'rows' => 18,
                'placeholder' => 'Enter full policy text. Plain text or simple HTML is supported.',
                'required' => true,
            ]) !!}
            @error('content')
                <span class="help-block text-danger" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
