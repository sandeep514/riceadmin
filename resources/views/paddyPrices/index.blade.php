@extends('layouts.main')

@section('content')
<style>
    .nonbasmatitabs .nav>li>a {
        padding: 10px 11px;
    }    
    .basmatitabs .nav>li>a {
        padding: 10px 11px;
    }
</style>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Paddy Prices
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('list.paddy.price') }}">Paddy Prices</a></li>
                <li class="active">List</li>
            </ol>
        </section>

        <section class="content">
            <div class="box-body">
                <form method="POST" action="{{ route('save.paddy.price') }}">
                    
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div>
                                <div class="form-group col-md-3">
                                    <label>State</label>
                                    <select class="form-control" name="state" id="paddy-state" required>
                                        <option value="">-- Select State --</option>
                                        @foreach($paddyStateModel as $v)
                                            <option value="{{ $v->id }}" {{ (int) old('state') === (int) $v->id ? 'selected' : '' }}>
                                                {{ $v->state }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Mandi</label>
                                    <select class="form-control" name="mandi" id="paddy-mandi" required>
                                        <option value="">-- Select Mandi --</option>
                                        @foreach($paddyMandiModel as $v)
                                            <option
                                                value="{{ $v->id }}"
                                                data-state-id="{{ $v->state_id }}"
                                                {{ (int) old('mandi') === (int) $v->id ? 'selected' : '' }}
                                            >
                                                {{ $v->mandi }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mandi')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Date</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        name="date"
                                        value="{{ old('date', date('Y-m-d')) }}"
                                        max="{{ date('Y-m-d') }}"
                                        required
                                    >
                                    @error('date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Crop Year</label>
                                    <select class="form-control" name="crop_year" required>
                                        @for($year = (int) date('Y'); $year >= (int) date('Y') - 5; $year--)
                                            <option value="{{ $year }}" {{ (int) old('crop_year', date('Y')) === $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('crop_year')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div>
                                <div class="form-group col-md-3">
                                    <label>Quality</label>
                                    <select class="form-control" name="quality_id" required>
                                        <option value="">-- Select Quality --</option>
                                        @foreach($quality as $v)
                                            <option value="{{ $v->id }}" {{ (int) old('quality_id') === (int) $v->id ? 'selected' : '' }}>
                                                {{ $v->type_label }} - {{ $v->quality }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('quality_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Min Hand Cutting Price</label>
                                    <input type="text" class="form-control" name="minHandCutting" value="{{ old('minHandCutting') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Max Hand Cutting Price</label>
                                    <input type="text" class="form-control" name="handCutting" value="{{ old('handCutting') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Min Machine Cutting Price</label>
                                    <input type="text" class="form-control" name="minMachineCutting" value="{{ old('minMachineCutting') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Max Machine Cutting Price</label>
                                    <input type="text" class="form-control" name="machineCutting" value="{{ old('machineCutting') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div>
                                <div class="form-group col-md-3">
                                    <label>Moisture</label>
                                    <input type="text" class="form-control" name="moisture" value="{{ old('moisture') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Total Arrival (Bags)</label>
                                    <input type="text" class="form-control" name="bags" value="{{ old('bags') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Change</label>
                                    <select class="form-control" name="change">
                                        @foreach(['Stable', 'Down', 'Up'] as $change)
                                            <option value="{{ $change }}" {{ old('change', 'Stable') === $change ? 'selected' : '' }}>{{ $change }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="submit" value="Submit" class="btn btn-primary btn-sm" style="float: right">
                    </div>
                </form>
                    <div class="responsiveTabs basmatitabs">
                        <div id="myTabContent" class="tab-content" >
                            <div class="">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <div class="row text-left" style="margin-top: 20px;">
                                            {{-- <a href="{{ route('paddy-prices.create') }}" class="btn btn-primary mb-3">Add New Price</a> --}}
                                            <div class="col-md-12 inputs">
                                                <div class="form-inline" style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                                    <span class="label label-{{ (int)($currentMarketStatus ?? 1) === 1 ? 'success' : ((int)($currentMarketStatus ?? 1) === 12 ? 'warning' : 'danger') }}" style="font-size: 13px; padding: 6px 10px;">
                                                        Market Paddy: {{ ucfirst($currentMarketLabel ?? 'open') }}
                                                    </span>
                                                    <div class="form-group">
                                                        <label for="market-paddy-status" style="margin-right: 6px;">Market Paddy</label>
                                                        <select id="market-paddy-status" class="form-control">
                                                            <option value="1" {{ (int)($currentMarketStatus ?? 1) === 1 ? 'selected' : '' }}>Open</option>
                                                            <option value="11" {{ (int)($currentMarketStatus ?? 1) === 11 ? 'selected' : '' }}>Close</option>
                                                            <option value="12" {{ (int)($currentMarketStatus ?? 1) === 12 ? 'selected' : '' }}>Hold</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <form method="POST" action="{{ route('save.paddy.price.defaults') }}" class="form-inline" style="margin-bottom: 15px; display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; background: #f9f9f9; padding: 10px; border: 1px solid #eee;">
                                                    @csrf
                                                    <div class="form-group">
                                                        <label for="default-view-by" style="margin-right: 6px;">Default View By</label>
                                                        <select id="default-view-by" name="default_view_by" class="form-control">
                                                            <option value="mandi" {{ old('default_view_by', $priceDefaults->default_view_by ?? 'mandi') === 'mandi' ? 'selected' : '' }}>Mandis</option>
                                                            <option value="crop" {{ old('default_view_by', $priceDefaults->default_view_by ?? 'mandi') === 'crop' ? 'selected' : '' }}>Crops</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                </form>
                                                <form method="GET" action="{{ route('list.paddy.price') }}" id="paddy-prices-filter-form" class="form-inline" style="margin-bottom: 15px;">
                                                    <div class="form-group" style="margin-right: 10px;">
                                                        <label for="from" style="margin-right: 6px;">From</label>
                                                        <input type="date" id="from" name="from" class="form-control" value="{{ $from ?? '' }}">
                                                    </div>
                                                    <div class="form-group" style="margin-right: 10px;">
                                                        <label for="to" style="margin-right: 6px;">To</label>
                                                        <input type="date" id="to" name="to" class="form-control" value="{{ $to ?? '' }}">
                                                    </div>
                                                    <div class="form-group" style="margin-right: 10px;">
                                                        <label for="paddy-prices-search" style="margin-right: 6px;">Search</label>
                                                        <input
                                                            type="search"
                                                            id="paddy-prices-search"
                                                            name="search"
                                                            class="form-control"
                                                            placeholder="Mandi, state, quality, price…"
                                                            value="{{ $search ?? '' }}"
                                                            autocomplete="off"
                                                            style="min-width: 220px;"
                                                        >
                                                    </div>
                                                    <button type="submit" class="btn btn-info btn-sm">Filter</button>
                                                    <a href="{{ route('list.paddy.price') }}" class="btn btn-default btn-sm">Clear</a>
                                                    <a href="{{ route('list.paddy.price', array_filter(['from' => $from ?? null, 'to' => $to ?? null, 'search' => $search ?? null, 'export' => 'excel'])) }}" class="btn btn-success btn-sm">
                                                        <i class="fa fa-file-excel-o"></i> Excel
                                                    </a>
                                                    <a href="{{ route('list.paddy.price', array_filter(['from' => $from ?? null, 'to' => $to ?? null, 'search' => $search ?? null, 'export' => 'pdf'])) }}" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-file-pdf-o"></i> PDF
                                                    </a>
                                                </form>
                                                <div class="table-responsive">
                                                    <table id="paddy-prices-table" class="table table-bordered table-striped" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Mandi</th>
                                                                <th>State</th>
                                                                <th>Quality</th>
                                                                <th>Crop Year</th>
                                                                <th>Min Hand</th>
                                                                <th>Max Hand</th>
                                                                <th>Min Machine</th>
                                                                <th>Max Machine</th>
                                                                <th>Moisture</th>
                                                                <th>Total Arrivals</th>
                                                                <th>Change</th>
                                                                <th>Created At (IST)</th>
                                                                <th>Updated At (IST)</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($paddyPrices as $paddyPrice)
                                                                <tr>
                                                                    <td>{{ $paddyPrice->id }}</td>
                                                                    <td>{{ optional($paddyPrice->getMandi_rel)->mandi }}</td>
                                                                    <td>{{ optional($paddyPrice->getState_rel)->state }}</td>
                                                                    <td>{{ optional($paddyPrice->quality_rel)->quality ? ($paddyPrice->quality_rel->type_label.' - '.$paddyPrice->quality_rel->quality) : '—' }}</td>
                                                                    <td>{{ $paddyPrice->crop_year }}</td>
                                                                    <td>{{ $paddyPrice->min_hand_cutting_price }}</td>
                                                                    <td>{{ $paddyPrice->hand_cutting_price }}</td>
                                                                    <td>{{ $paddyPrice->min_machine_cutting_price }}</td>
                                                                    <td>{{ $paddyPrice->machine_cutting_price }}</td>
                                                                    <td>{{ $paddyPrice->moisture }}</td>
                                                                    <td>{{ $paddyPrice->total_arrivals }}</td>
                                                                    <td>{{ $paddyPrice->change }}</td>
                                                                    <td>
                                                                        {{ $paddyPrice->created_at ? $paddyPrice->created_at->copy()->timezone('Asia/Kolkata')->format('Y-m-d H:i:s') : '' }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $paddyPrice->updated_at ? $paddyPrice->updated_at->copy()->timezone('Asia/Kolkata')->format('Y-m-d H:i:s') : '' }}
                                                                    </td>
                                                                    <td>{{ $paddyPrice->status ? 'Active' : 'Inactive' }}</td>
                                                                    <td style="white-space: nowrap;">
                                                                        <a
                                                                            href="{{ route('edit.paddy.price', array_filter(['id' => $paddyPrice->id, 'from' => $from ?? null, 'to' => $to ?? null, 'search' => $search ?? null])) }}"
                                                                            class="btn btn-primary btn-xs"
                                                                        >
                                                                            <i class="fa fa-pencil"></i> Edit
                                                                        </a>
                                                                        <form
                                                                            class="js-delete-paddy-price-form"
                                                                            method="POST"
                                                                            action="{{ route('delete.paddy.price', $paddyPrice->id) }}"
                                                                            style="display:inline;"
                                                                        >
                                                                            @csrf
                                                                            <input type="hidden" name="pin" class="js-delete-paddy-price-pin" value="">
                                                                            @if(!empty($from))
                                                                                <input type="hidden" name="from" value="{{ $from }}">
                                                                            @endif
                                                                            @if(!empty($to))
                                                                                <input type="hidden" name="to" value="{{ $to }}">
                                                                            @endif
                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-danger btn-xs js-delete-paddy-price-btn"
                                                                                data-id="{{ $paddyPrice->id }}"
                                                                            >
                                                                                <i class="fa fa-trash"></i> Delete
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="text-center" style="margin-top: 10px;">
                                                    {{ $paddyPrices->onEachSide(1)->links() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(function () {
        $('#paddy-prices-table').DataTable({
            // Server already paginates 30/page — do not double-paginate in DataTables.
            // Search is server-side (see #paddy-prices-search) so it covers all pages.
            paging: false,
            info: false,
            searching: false,
            order: [[0, 'desc']],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'csv',
                    title: 'paddy-prices',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'excel',
                    title: 'paddy-prices',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'pdf',
                    title: 'Paddy Prices',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'print',
                    exportOptions: { columns: ':not(:last-child)' }
                }
            ],
            columnDefs: [
                { orderable: false, targets: [15] }
            ],
            language: {
                emptyTable: 'No paddy prices found for the selected filters.'
            }
        });

        var $search = $('#paddy-prices-search');
        var $filterForm = $('#paddy-prices-filter-form');
        var searchTimer = null;
        var lastSubmittedSearch = String($search.val() || '');

        function submitSearchIfChanged() {
            var current = String($search.val() || '').trim();
            if (current === lastSubmittedSearch) {
                return;
            }
            lastSubmittedSearch = current;
            // Reset to page 1 when search changes.
            $filterForm.find('input[name="page"]').remove();
            $filterForm.trigger('submit');
        }

        $search.on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(submitSearchIfChanged, 450);
        });

        $search.on('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                clearTimeout(searchTimer);
                submitSearchIfChanged();
            }
        });

        var $state = $('#paddy-state');
        var $mandi = $('#paddy-mandi');
        var mandiOptions = $mandi.find('option[data-state-id]').clone();
        var oldMandi = @json((string) old('mandi', ''));

        function loadMandis(stateId, selectedMandi) {
            $mandi.empty().append('<option value="">-- Select Mandi --</option>');

            if (!stateId) {
                $mandi.prop('disabled', true);
                return;
            }

            mandiOptions.each(function () {
                var $option = $(this);
                if (String($option.data('state-id')) === String(stateId)) {
                    $mandi.append($option.clone());
                }
            });

            $mandi.prop('disabled', false);
            if (selectedMandi && $mandi.find('option[value="' + selectedMandi + '"]').length) {
                $mandi.val(selectedMandi);
            } else {
                $mandi.val('');
            }
        }

        $state.on('change', function () {
            loadMandis($(this).val(), '');
        });

        loadMandis($state.val(), oldMandi);

        $('#market-paddy-status').on('change', function () {
            var status = $(this).val();
            var baseUrl = @json(route('update.paddy.market.status', ['tradeStatus' => '__STATUS__']));
            window.location.href = baseUrl.replace('__STATUS__', status);
        });

        $(document).on('click', '.js-delete-paddy-price-btn', function () {
            var $btn = $(this);
            var $form = $btn.closest('.js-delete-paddy-price-form');
            var priceId = $btn.data('id');

            if (typeof toastr !== 'undefined') {
                toastr.warning('This action will permanently delete paddy price #' + priceId + '. Enter PIN to continue.', 'Confirm delete');
            }

            var confirmed = window.confirm('Are you sure you want to delete paddy price #' + priceId + '?');
            if (!confirmed) return;

            var pin = window.prompt('Enter security PIN to confirm delete:');
            if (pin === null) return;

            pin = String(pin).trim();
            if (pin !== '22334455') {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Invalid PIN. Paddy price was not deleted.', 'Error');
                } else {
                    alert('Invalid PIN. Paddy price was not deleted.');
                }
                return;
            }

            $form.find('.js-delete-paddy-price-pin').val(pin);
            $form.trigger('submit');
        });
    });
</script>
@endsection