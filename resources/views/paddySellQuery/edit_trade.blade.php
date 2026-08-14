@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Edit Paddy Trade
            <small>#{{ $trade->id }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('list.paddy.trades') }}">Paddy Trades</a></li>
            <li class="active">Edit</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-12">
                <a href="{{ route('list.paddy.trades') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                <a href="{{ route('view.paddy.trade', $trade->id) }}" class="btn btn-info btn-sm">
                    <i class="fa fa-eye"></i> View
                </a>
            </div>
        </div>

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Paddy Trade details</h3>
                <div class="pull-right">
                    <span class="label label-{{ $trade->status_badge_class }}">{{ $trade->status_label }}</span>
                </div>
            </div>
            <form action="{{ route('update.paddy.trade', $trade->id) }}" method="POST" enctype="multipart/form-data">
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

                    @if($trade->paddy_sell_query_id)
                        <p class="help-block">
                            Linked sell query:
                            <a href="{{ route('view.paddy.sell.query', $trade->paddy_sell_query_id) }}">#{{ $trade->paddy_sell_query_id }}</a>
                        </p>
                    @endif

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Rice Type (Category) <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-control" required>
                                @foreach($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" {{ (string) old('category', $trade->category) === (string) $value ? 'selected' : '' }}>
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
                                        {{ (string) old('quality', $trade->quality) === (string) $quality->id ? 'selected' : '' }}
                                    >
                                        {{ $quality->quality }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Hand / Combined <span class="text-danger">*</span></label>
                            @php $selectedHand = old('hand_combined', $trade->hand_combined ?: 'Combined'); @endphp
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
                            <label>Packing <small class="text-muted">(optional text)</small></label>
                            <input type="text" name="packing" class="form-control"
                                   value="{{ old('packing', $trade->packing) }}"
                                   placeholder="e.g. 50Kg PP+inner">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="text" name="quantity" class="form-control"
                                   value="{{ old('quantity', $trade->quantity) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Rate <span class="text-danger">*</span></label>
                            <input type="text" name="rate" class="form-control"
                                   value="{{ old('rate', $trade->rate) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Valid Days <span class="text-danger">*</span></label>
                            <input type="text" name="valid_days" class="form-control"
                                   value="{{ old('valid_days', $trade->valid_days) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control"
                                   value="{{ old('location', $trade->location) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control"
                                   value="{{ old('contact_person', $trade->contact_person) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="contact_number" class="form-control"
                                   value="{{ old('contact_number', $trade->contact_number) }}" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Type</label>
                            <input type="text" name="type" class="form-control"
                                   value="{{ old('type', $trade->type ?: 'admin') }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Is New</label>
                            <select name="is_new" class="form-control">
                                <option value="0" {{ (string) old('is_new', (int) $trade->is_new) === '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ (string) old('is_new', (int) $trade->is_new) === '1' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>

                        <div class="form-group col-md-8">
                            <label>Linked user <small class="text-muted">(optional)</small></label>
                            <select name="user_id" class="form-control select2" style="width: 100%;">
                                <option value="">No user (admin created)</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (string) old('user_id', $trade->user_id) === (string) $user->id ? 'selected' : '' }}>
                                        #{{ $user->id }} — {{ $user->name ?: 'N/A' }}
                                        @if($user->email) ({{ $user->email }}) @endif
                                        @if($user->companyname) — {{ $user->companyname }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-12">
                            <label>Remarks <small class="text-muted">(optional)</small></label>
                            <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $trade->remarks) }}</textarea>
                        </div>

                        <div class="form-group col-md-12">
                            <label>Additional Information <small class="text-muted">(optional)</small></label>
                            <textarea name="additional_information" class="form-control" rows="3" placeholder="Additional information">{{ old('additional_information', $trade->additional_information) }}</textarea>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Lot Number <small class="text-muted">(optional)</small></label>
                            <input type="text" name="lot_number" class="form-control"
                                   value="{{ old('lot_number', $trade->lot_number) }}"
                                   placeholder="e.g. LOT-001">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Crop Year <small class="text-muted">(optional)</small></label>
                            <input type="text" name="crop_year" class="form-control"
                                   value="{{ old('crop_year', $trade->crop_year) }}"
                                   placeholder="e.g. 2025">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Image <small class="text-muted">(optional — leave empty to keep current)</small></label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            @if($trade->image)
                                <div style="margin-top: 10px;">
                                    <p class="help-block" style="margin-bottom: 6px;">Current image:</p>
                                    <a href="{{ $trade->image_url }}" target="_blank">
                                        <img src="{{ $trade->image_url }}" alt="current" style="max-width: 140px; max-height: 140px; object-fit: contain; border: 1px solid #ddd; padding: 4px;">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-save"></i> Update Paddy Trade
                    </button>
                    <a href="{{ route('list.paddy.trades') }}" class="btn btn-default">Cancel</a>
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
                }
            }
        }

        $(document).ready(function () {
            filterQualitiesByCategory();
            $('#category').on('change', filterQualitiesByCategory);
            if ($.fn.select2) {
                $('.select2').select2({ width: '100%', placeholder: 'Select user (optional)' });
            }
        });
    })();
</script>
@endsection
