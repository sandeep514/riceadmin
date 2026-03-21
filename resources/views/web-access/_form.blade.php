<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('role_id') has-error @enderror">
            {!! Form::label('role_id','Role*') !!}
            @if(isset($access))
                {!! Form::select('role_id', $roles, $access->role_id, ['class'=>'form-control','id'=>'role_id','disabled'=>'disabled']) !!}
                {!! Form::hidden('role_id', $access->role_id) !!}
            @else
                {!! Form::select('role_id', $roles, old('role_id'), ['class'=>'form-control','id'=>'role_id','placeholder'=>'Select Role']) !!}
            @endif
            @error('role_id')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('category_id') has-error @enderror">
            {!! Form::label('category_id','Category') !!}
            @if(isset($access))
                {!! Form::select('category_id', $categories, $access->category_id, ['class'=>'form-control','id'=>'category_id','disabled'=>'disabled']) !!}
                {!! Form::hidden('category_id', $access->category_id) !!}
            @else
                {!! Form::select('category_id', ['' => 'Select Category'], old('category_id'), ['class'=>'form-control','id'=>'category_id','disabled'=>'disabled']) !!}
            @endif
            @error('category_id')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
            <small class="help-block">Please select a role first to load categories</small>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            {!! Form::label('plan_id','Plan') !!}
            @if(isset($access))
                {!! Form::hidden('plan_id', $access->plan_id, ['id'=>'plan_id']) !!}
            @else
                <input type="hidden" name="plan_id" id="plan_id" value="">
            @endif
            <input type="text" id="plan_display"
                   class="form-control"
                   value="{{ isset($access) && $access->plan ? $access->plan->title : '' }}"
                   placeholder="Auto-filled from Role + Category"
                   readonly disabled
                   style="background:#f5f5f5; cursor:not-allowed;">
            <small class="help-block">Automatically selected based on Role &amp; Category.</small>
        </div>
    </div>
    @php
        $selectedYears = [];
        if(isset($accesses) && $accesses->count() > 0){
            $selectedYears = $accesses->first()->allowed_years ?? [];
        } elseif(isset($access) && isset($access->allowed_years)) {
            $selectedYears = $access->allowed_years ?? [];
        }
        $yearsFromDb = \App\LivePrice::query()
            ->whereNotNull('cropYear')
            ->select('cropYear')
            ->distinct()
            ->orderBy('cropYear', 'desc')
            ->limit(5)
            ->pluck('cropYear')
            ->map(fn($y) => (int) $y)
            ->toArray();
        if (empty($yearsFromDb)) {
            $currentYear = now()->year;
            $yearsFromDb = [$currentYear, $currentYear-1, $currentYear-2, $currentYear-3, $currentYear-4];
        }
    @endphp
    <div class="row">
        <div class="form-group col-md-12 @error('allowed_years') has-error @enderror">
            <label>Allowed Years</label>
            <div class="checkbox">
                @foreach($yearsFromDb as $y)
                    <label style="margin-right: 12px;">
                        <input type="checkbox" name="allowed_years[]" value="{{ $y }}" {{ in_array($y, $selectedYears ?? []) ? 'checked' : '' }}>
                        {{ $y }}
                    </label>
                @endforeach
            </div>
            <small class="help-block">Select years this role/category/plan can access.</small>
            @error('allowed_years')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <label>Menu Items & CRUD Permissions*</label>
            <div class="table-responsive" style="margin-top: 10px;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Menu Item</th>
                            <th style="width: 17.5%; text-align: center;">Create</th>
                            <th style="width: 17.5%; text-align: center;">Read</th>
                            <th style="width: 17.5%; text-align: center;">Update</th>
                            <th style="width: 17.5%; text-align: center;">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                            @php
                                $existingAccess = isset($accesses) ? $accesses->where('web_side_menu_id', $menu->id)->first() : null;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $menu->title }}</strong>
                                    @if($menu->sub_title)
                                        <br><small class="text-muted">{{ $menu->sub_title }}</small>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="menu_permissions[{{ $menu->id }}][can_create]" value="1" 
                                           {{ isset($existingAccess) && $existingAccess->can_create ? 'checked' : '' }}>
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="menu_permissions[{{ $menu->id }}][can_read]" value="1" 
                                           {{ isset($existingAccess) && $existingAccess->can_read ? 'checked' : '' }}>
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="menu_permissions[{{ $menu->id }}][can_update]" value="1" 
                                           {{ isset($existingAccess) && $existingAccess->can_update ? 'checked' : '' }}>
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="menu_permissions[{{ $menu->id }}][can_delete]" value="1" 
                                           {{ isset($existingAccess) && $existingAccess->can_delete ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <small class="help-block">Select at least one permission for at least one menu item.</small>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6 @error('status') has-error @enderror">
            {!! Form::label('status','Status*') !!}
            {!! Form::select('status',[1=>'Active',0=>'Inactive'],isset($access) ? $access->status : 1,['class'=>'form-control','id'=>'status']) !!}
            @error('status')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
