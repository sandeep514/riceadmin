@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Convert to Paddy Trade
            <small>Sell Query #{{ $query->id }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('list.paddy.sell.queries') }}">Paddy Sell Queries</a></li>
            <li class="active">Convert to Trade</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-12">
                <a href="{{ route('list.paddy.sell.queries') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                <a href="{{ route('view.paddy.sell.query', $query->id) }}" class="btn btn-info btn-sm">
                    <i class="fa fa-eye"></i> View sell query
                </a>
            </div>
        </div>

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Paddy Trade details (prefilled — edit as needed)</h3>
            </div>
            <form action="{{ route('save.convert.paddy.sell.query', $query->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="box-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="margin-bottom-none" style="padding-left: 18px; margin-bottom: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Rice Type (Category) <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-control" required>
                                @foreach($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" {{ (string) old('category', $query->category) === (string) $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Quality <span class="text-danger">*</span></label>
                            <select name="quality" id="quality" class="form-control" required>
                                <option value="">Select quality</option>
                                @foreach($qualities as $quality)
                                    <option
                                        value="{{ $quality->id }}"
                                        data-type="{{ $quality->type }}"
                                        data-name="{{ $quality->quality }}"
                                        {{ (string) old('quality', $query->quality) === (string) $quality->id ? 'selected' : '' }}
                                    >
                                        {{ $quality->quality }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Quality Name</label>
                            <input type="text" name="quality_name" id="quality_name" class="form-control"
                                   value="{{ old('quality_name', $query->quality_name) }}"
                                   placeholder="e.g. 1121">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Hand / Combined <span class="text-danger">*</span></label>
                            @php $selectedHand = old('hand_combined', $query->hand_combined); @endphp
                            <select name="hand_combined" class="form-control" required>
                                @foreach($handCombinedOptions as $value => $label)
                                    <option value="{{ $value }}" {{ (string) $selectedHand === (string) $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                                @if($selectedHand && !isset($handCombinedOptions[$selectedHand]))
                                    <option value="{{ $selectedHand }}" selected>{{ $selectedHand }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Packing <small class="text-muted">(optional)</small></label>
                            <input type="text" name="packing" class="form-control"
                                   value="{{ old('packing', $query->packing) }}"
                                   placeholder="e.g. 50Kg PP+inner">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="text" name="quantity" class="form-control"
                                   value="{{ old('quantity', $query->quantity) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Rate <span class="text-danger">*</span></label>
                            <input type="text" name="rate" class="form-control"
                                   value="{{ old('rate', $query->rate) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Valid Days <span class="text-danger">*</span></label>
                            <input type="text" name="valid_days" class="form-control"
                                   value="{{ old('valid_days', $query->valid_days) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control"
                                   value="{{ old('location', $query->location) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control"
                                   value="{{ old('contact_person', $query->contact_person) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="contact_number" class="form-control"
                                   value="{{ old('contact_number', $query->contact_number) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Type</label>
                            <input type="text" name="type" class="form-control"
                                   value="{{ old('type', $query->type ?: 'web') }}">
                        </div>

                        <div class="form-group col-md-8">
                            <label>Remarks <small class="text-muted">(optional)</small></label>
                            <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Image <small class="text-muted">(optional — leave empty to keep existing)</small></label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            @if($query->image)
                                <div style="margin-top: 10px;">
                                    <p class="help-block" style="margin-bottom: 6px;">Current image:</p>
                                    <a href="{{ $query->image_url }}" target="_blank">
                                        <img src="{{ $query->image_url }}" alt="current" style="max-width: 140px; max-height: 140px; object-fit: contain; border: 1px solid #ddd; padding: 4px;">
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="form-group col-md-6">
                            <label>Source user</label>
                            <p class="form-control-static">
                                @if($query->user)
                                    {{ $query->user->name ?? $query->user->email ?? ('#'.$query->user_id) }}
                                    <small class="text-muted">(ID: {{ $query->user_id }})</small>
                                @else
                                    {{ $query->user_id ?: '-' }}
                                @endif
                            </p>
                            <p class="help-block">Original seller from the sell query (not editable).</p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-exchange"></i> Convert to Paddy Trade
                    </button>
                    <a href="{{ route('list.paddy.sell.queries') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@section('javascript')
<script>
    (function () {
        function filterQualitiesByCategory() {
            var category = $('#category').val();
            var $quality = $('#quality');
            var selected = $quality.val();

            $quality.find('option').each(function () {
                var $opt = $(this);
                if (!$opt.val()) {
                    $opt.show();
                    return;
                }
                var type = String($opt.data('type') || '');
                if (!category || type === String(category)) {
                    $opt.show();
                } else {
                    $opt.hide();
                }
            });

            if (selected) {
                var selectedType = String($quality.find('option:selected').data('type') || '');
                if (category && selectedType && selectedType !== String(category)) {
                    $quality.val('');
                    $('#quality_name').val('');
                }
            }
        }

        $(document).ready(function () {
            filterQualitiesByCategory();

            $('#category').on('change', function () {
                filterQualitiesByCategory();
            });

            $('#quality').on('change', function () {
                var name = $(this).find('option:selected').data('name') || '';
                if (name) {
                    $('#quality_name').val(name);
                }
            });
        });
    })();
</script>
@endsection
