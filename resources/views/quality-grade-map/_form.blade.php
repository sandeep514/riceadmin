<div class="box-body">
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="form-group col-md-12 @error('quality_type') has-error @enderror">
                    <label for="quality_type">Category <span class="text-danger">*</span></label>
                    <select name="quality_type" id="quality_type" class="form-control">
                        <option value="">-- Select Basmati / Non-Basmati --</option>
                        <option value="basmati" {{ (isset($model) && $model->quality_type == 'basmati') ? 'selected' : (old('quality_type') == 'basmati' ? 'selected' : '') }}>Basmati</option>
                        <option value="non-basmati" {{ (isset($model) && $model->quality_type == 'non-basmati') ? 'selected' : (old('quality_type') == 'non-basmati' ? 'selected' : '') }}>Non-Basmati</option>
                    </select>
                    @error('quality_type')
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-12 @error('rice_name_id') has-error @enderror">
                    <label for="rice_name_id">Quality (Rice Name) <span class="text-danger">*</span></label>
                    <select name="rice_name_id" id="rice_name_id" class="form-control select2-basic">
                        <option value="">-- Select category first --</option>
                        @if(isset($riceNames))
                            @foreach($riceNames as $id => $name)
                                <option value="{{ $id }}" {{ (isset($model) && $model->rice_name_id == $id) ? 'selected' : (old('rice_name_id') == $id ? 'selected' : '') }}>{{ $name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('rice_name_id')
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-12 @error('form_id') has-error @enderror">
                    <label for="form_id">Form <span class="text-danger">*</span></label>
                    <select name="form_id" id="form_id" class="form-control select2-basic">
                        <option value="">-- Select rice form --</option>
                        @if(isset($riceForms))
                            @foreach($riceForms as $id => $formName)
                                <option value="{{ $id }}" {{ (isset($model) && $model->form_id == $id) ? 'selected' : (old('form_id') == $id ? 'selected' : '') }}>{{ $formName }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('form_id')
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-12 @error('wand_ids') has-error @enderror">
                    <label style="display:block;">
                        Grades (Wands)
                        <span class="pull-right">
                            <button type="button" id="wand_check_all" class="btn btn-xs btn-default">Check All</button>
                            <button type="button" id="wand_uncheck_all" class="btn btn-xs btn-default">Uncheck All</button>
                        </span>
                    </label>
                    <div id="wand_grade_grid" class="wand-grade-grid">
                        <p class="text-muted" id="wand_grade_placeholder" style="margin:8px 0;">Select a quality to load grades.</p>
                    </div>
                    <p class="help-block">Select one or more wand grades for this quality + form (e.g. 4 Wands, 2 Short Wands).</p>
                    @error('wand_ids')
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                    @error('wand_ids.*')
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-solid used-wands-panel">
                <div class="box-header with-border">
                    <h3 class="box-title" style="font-size:15px;">
                        <i class="fa fa-link"></i> Already used in trades
                    </h3>
                </div>
                <div class="box-body" id="used_wands_panel">
                    <p class="text-muted" id="used_wands_placeholder" style="margin:0;">
                        Select quality and form to see grades already used on trades / sell queries.
                    </p>
                    <ul id="used_wands_list" class="used-wands-list" style="display:none;"></ul>
                    <p id="used_wands_empty" class="text-muted" style="display:none; margin:0;">
                        No grades found on trades for this quality + form yet.
                    </p>
                </div>
                <div class="box-footer" style="padding:8px 10px;">
                    <small class="text-muted">Hint from existing trades — click a grade to tick it on the left.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wand-grade-grid {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -6px;
        min-height: 48px;
        border: 1px solid #d2d6de;
        border-radius: 3px;
        padding: 10px 4px 4px;
        background: #fafafa;
    }
    .wand-grade-grid .wand-grade-item {
        flex: 0 0 20%;
        max-width: 20%;
        padding: 0 6px 10px;
        box-sizing: border-box;
    }
    .wand-grade-grid .wand-grade-card {
        display: block;
        margin: 0;
        padding: 10px 12px;
        background: #fff;
        border: 1px solid #d2d6de;
        border-radius: 3px;
        cursor: pointer;
        font-weight: normal;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: border-color .15s, background .15s;
    }
    .wand-grade-grid .wand-grade-card:hover {
        border-color: #3c8dbc;
    }
    .wand-grade-grid .wand-grade-card.is-checked {
        border-color: #3c8dbc;
        background: #eaf4fb;
    }
    .wand-grade-grid .wand-grade-card.is-used-hint {
        box-shadow: inset 3px 0 0 #f39c12;
    }
    .wand-grade-grid .wand-grade-card input {
        margin-right: 6px;
        vertical-align: middle;
    }
    .used-wands-panel {
        border: 1px solid #d2d6de;
        position: sticky;
        top: 70px;
    }
    .used-wands-list {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 420px;
        overflow-y: auto;
    }
    .used-wands-list li {
        margin: 0 0 6px;
    }
    .used-wands-list .used-wand-btn {
        display: block;
        width: 100%;
        text-align: left;
        border: 1px solid #f0ad4e;
        background: #fff8ee;
        color: #8a6d3b;
        border-radius: 3px;
        padding: 8px 10px;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .used-wands-list .used-wand-btn:hover {
        background: #fcf0de;
        border-color: #eb9316;
    }
    .used-wands-list .used-wand-btn.is-selected {
        background: #eaf4fb;
        border-color: #3c8dbc;
        color: #31708f;
    }
    @media (max-width: 991px) {
        .wand-grade-grid .wand-grade-item {
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }
        .used-wands-panel {
            position: static;
            margin-top: 15px;
        }
    }
    @media (max-width: 767px) {
        .wand-grade-grid .wand-grade-item {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
</style>
