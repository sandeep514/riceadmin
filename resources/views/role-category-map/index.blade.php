@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Role Category Map
                <small>Master</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Role Category Map</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Map Categories With Role</h3>
                        </div>

                        <form method="GET" action="{{ route('role-category-map.index') }}">
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="role_id_filter">Select Role</label>
                                        <select class="form-control" id="role_id_filter" name="role_id" onchange="this.form.submit()">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ (int)$selectedRoleId === (int)$role->id ? 'selected' : '' }}>
                                                    {{ $role->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('role-category-map.save') }}">
                            @csrf
                            <input type="hidden" name="role_id" value="{{ $selectedRoleId }}">
                            <div class="box-body">
                                @if($selectedRoleId)
                                    <div class="form-group @error('categories') has-error @enderror">
                                        <label>Categories</label>
                                        <div class="row">
                                            @forelse($categories as $category)
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox"
                                                                   name="categories[]"
                                                                   value="{{ $category->id }}"
                                                                   {{ in_array((int)$category->id, old('categories', $selectedCategoryIds ?? [])) ? 'checked' : '' }}>
                                                            {{ $category->category }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-md-12">
                                                    <p class="text-muted">No active categories found.</p>
                                                </div>
                                            @endforelse
                                        </div>
                                        @error('categories')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                        @error('categories.*')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @else
                                    <p class="text-muted">No role available for mapping.</p>
                                @endif
                            </div>

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary" {{ !$selectedRoleId ? 'disabled' : '' }}>Save Mapping</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
