@extends('layouts.main')

@section('content')
<style>
    .vendor-bulk-tabs > li > a {
        padding: 8px 12px;
        font-size: 13px;
    }
    .vendor-bulk-panel {
        border: 1px solid #ddd;
        border-top: none;
        padding: 18px 16px;
        background: #fff;
        min-height: 220px;
    }
    .vendor-bulk-result {
        margin-top: 16px;
        max-height: 260px;
        overflow-y: auto;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Import Vendors
            <small>Other Services Excel bulk create</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Import Vendors</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Upload vendors / service providers by category</h3>
                        <div class="box-tools pull-right">
                            <a href="{{ route('vendor-bulk-import.sample') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-download"></i> Download sample Excel
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="help-block" style="margin-top:0;">
                            Each tab is an Other Services category (same list as the website).
                            Upload fills <strong>Web Users</strong> + business profile for that service.
                            Login credentials are <strong>not</strong> emailed.
                        </p>

                        @if($categories->isEmpty())
                            <div class="alert alert-warning">
                                No active Vendor / Service Provider categories found in Role Category Map.
                            </div>
                        @else
                            <ul class="nav nav-tabs vendor-bulk-tabs" role="tablist">
                                @foreach($categories as $i => $cat)
                                    <li role="presentation" class="{{ (int) $activeTab === (int) $cat->id || ($activeTab === 0 && $i === 0) ? 'active' : '' }}">
                                        <a href="#vendor-cat-{{ $cat->id }}" aria-controls="vendor-cat-{{ $cat->id }}" role="tab" data-toggle="tab">
                                            {{ $cat->category }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content vendor-bulk-panel">
                                @foreach($categories as $i => $cat)
                                    <div
                                        role="tabpanel"
                                        class="tab-pane {{ (int) $activeTab === (int) $cat->id || ($activeTab === 0 && $i === 0) ? 'active' : '' }}"
                                        id="vendor-cat-{{ $cat->id }}"
                                    >
                                        <h4 style="margin-top:0;">{{ $cat->category }}</h4>
                                        <p class="text-muted">
                                            Rows will be created with <code>selected_category = {{ $cat->id }}</code>.
                                        </p>

                                        {!! Form::open([
                                            'route' => 'vendor-bulk-import.import',
                                            'files' => true,
                                            'class' => 'form-horizontal',
                                        ]) !!}
                                            <input type="hidden" name="category_id" value="{{ $cat->id }}">

                                            <div class="form-group @error('file') has-error @enderror">
                                                <label class="col-sm-3 control-label">Excel file *</label>
                                                <div class="col-sm-6">
                                                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                                    @error('file')
                                                        <span class="help-block text-danger">{{ $message }}</span>
                                                    @enderror
                                                    <p class="help-block">
                                                        Columns: Name, Email, Mobile, Company Name, Contact Person, Contact Mobile, Address, City, State, Product
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-3 control-label">Listing status *</label>
                                                <div class="col-sm-6">
                                                    <label class="radio-inline" style="margin-right: 18px;">
                                                        <input type="radio" name="is_active_listing" value="1" checked>
                                                        Active
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="is_active_listing" value="0">
                                                        Inactive
                                                    </label>
                                                    <p class="help-block">Active vendors appear on the website listing for this service.</p>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="col-sm-offset-3 col-sm-6">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-upload"></i> Import for {{ $cat->category }}
                                                    </button>
                                                    <a href="{{ route('vendor-bulk-import.sample') }}" class="btn btn-default">
                                                        <i class="fa fa-file-excel-o"></i> Sample Excel
                                                    </a>
                                                </div>
                                            </div>
                                        {!! Form::close() !!}

                                        @if(!empty($importResult) && (int) ($importResult['category_id'] ?? 0) === (int) $cat->id)
                                            <div class="vendor-bulk-result">
                                                <div class="alert alert-info" style="margin-bottom: 8px;">
                                                    Last import for <strong>{{ $importResult['category_name'] ?? $cat->category }}</strong>:
                                                    created {{ (int) ($importResult['created'] ?? 0) }},
                                                    skipped {{ (int) ($importResult['skipped'] ?? 0) }}.
                                                </div>
                                                @if(!empty($importResult['errors']))
                                                    <ul class="list-unstyled" style="font-size: 12px;">
                                                        @foreach($importResult['errors'] as $err)
                                                            <li class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ $err }}</li>
                                                        @endforeach
                                                        @if((int) ($importResult['error_total'] ?? 0) > count($importResult['errors']))
                                                            <li class="text-muted">…and more (showing first {{ count($importResult['errors']) }}).</li>
                                                        @endif
                                                    </ul>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
