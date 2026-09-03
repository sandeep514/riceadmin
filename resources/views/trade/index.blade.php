@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Trade
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Trade</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Trade</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-11" style="display: inline-flex;">
                                <div>
                                    <a href="{{ route('master.update.trade.create' , ['tradeStatus'=> 1 ]) }}" class="btn btn-info btn-sm">Open Market</a>
                                </div>

                                <div>
                                    <a href="{{ route('master.update.trade.create' , ['tradeStatus'=> 11 ]) }}" class="btn btn-info btn-sm">Close Market</a>
                                </div>

                                <div>
                                    <a href="{{ route('master.update.trade.create' , ['tradeStatus'=> 12 ]) }}" class="btn btn-info btn-sm">Hold Market</a>
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="{{ route('master.trade.create') }}" class="btn btn-info btn-sm">Create</a>
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-12" style="display:flex; gap:10px; flex-wrap: wrap; align-items:center;">
                                <a href="javascript:void(0)" class="btn btn-default js-trade-note" data-type="closing">
                                    Closing <span class="badge">{{ $closingCount ?? 0 }}</span>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-default js-trade-note" data-type="sold">
                                    Sold <span class="badge">{{ $soldCount ?? 0 }}</span>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-default js-trade-note" data-type="expired">
                                    Expired <span class="badge">{{ $expiredCount ?? 0 }}</span>
                                </a>
                                <span class="btn btn-default" style="cursor: default;">
                                    Active Buy Trades <span class="badge">{{ $activeBuyCount ?? 0 }}</span>
                                </span>
                                <span class="btn btn-default" style="cursor: default;">
                                    Active Sell Trades <span class="badge">{{ $activeSellCount ?? 0 }}</span>
                                </span>
                                <button type="button" class="btn btn-warning btn-sm" id="js-bulk-valid-days-btn" disabled>
                                    Update Valid Date (<span id="js-bulk-valid-days-count">0</span>)
                                </button>
                            </div>
                        </div>

                        <div class="modal fade" id="bulkValidDaysModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title">Update Valid Date</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>Set a new valid date/time for <strong id="js-bulk-valid-days-selected">0</strong> selected trade(s).</p>
                                        <div class="form-group">
                                            <label for="bulkValidityInput">Valid Till</label>
                                            <input type="datetime-local" id="bulkValidityInput" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="js-bulk-valid-days-submit">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal fade" id="tradeNoteModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title">Notice</h4>
                                    </div>
                                    <form id="tradePurgeForm" method="POST" action="{{ route('master.trade.purge.old') }}">
                                        @csrf
                                        <input type="hidden" name="type" id="purgeType" value="">
                                        <div class="modal-body">
                                            <p id="tradeNoteText"></p>
                                            <p class="text-danger"><small>Note: Do you realy want to delete the records ?</small></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Confirm Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <div class="col-md-12">
                                    <table id="example2" class="display" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 36px;">
                                                    <input type="checkbox" id="js-trade-select-all" title="Select all">
                                                </th>
                                                <th style="text-align: center">SNTC Lot No</th>
                                                <th style="text-align: center">Trade ID</th>
                                                <th style="text-align: center">Trade Type</th>
                                                <th style="text-align: center">Quality Type</th>
                                                <th style="text-align: center">Quality</th>
                                                <th style="text-align: center">QualityForm</th>
                                                <th style="text-align: center">Link With Live Price</th>
                                                <th style="text-align: center">Link With Live Price state</th>
                                                <th style="text-align: center">Grade</th>
                                                <th style="text-align: center">Packing</th>
                                                <th style="text-align: center">Quantity</th>
                                                <th style="text-align: center">OfferPrice</th>
                                                <th style="text-align: center">ValidDays</th>
                                                <th style="text-align: center">Packing File</th>
                                                <th style="text-align: center">Uncooked File</th>
                                                <th style="text-align: center">Cooked File</th>
                                                <th style="text-align: center">Status</th>
                                                <th style="text-align: center">Created at</th>
                                                <th style="text-align: center">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($sellQueries as $k => $v)
                                                <tr data-trade-id="{{ $v->id }}">
                                                    <td style="text-align:center;">
                                                        <input type="checkbox" class="js-trade-select" value="{{ $v->id }}">
                                                    </td>
                                                    <td>{{ $v->sntcLotNo }}</td>
                                                    <td>Trade_{{ $v->id }}</td>
                                                    <td>{{ ($v->tradeType == 1)? 'Buy' : 'Sell' }}</td>
                                                    <td>{{ ($v->quality_type == 1)? 'Basmati' : 'Non-Basmati'  }}</td>
                                                    <td>{{ ($v->RiceNameData->name )?? '--'}}</td>
                                                    <td>{{ ($v->RiceFormMilestone3->name )?? '--'}}</td>
                                                    <td>{{ ($v->RiceFormData->form_name )?? '--'}}</td>
                                                    <td>{{ ($v->stateLinkWithLivePrice )?? '--'}}</td>
                                                    <td>{{ $v->riceGrade?->getWandType?->type ?? '--' }} {{ $v->riceGrade?->value ?? '--' }}</td>
                                                    @php
                                                        $isBranded = (int) ($v->packingStreamType ?? 1) === 2;
                                                        $rowPackings = $isBranded
                                                            ? $publicPackings
                                                            : (((int) $v->tradeType === 2) ? $buyerPackings : $sellerPackings);
                                                        if ($isBranded) {
                                                            $currentPackingLabel = \App\TradeQueriesINR::packingLabel($v->RicePackingPublic ?: (object) []);
                                                        } else {
                                                            $currentPackingLabel = ((int) $v->tradeType === 2)
                                                                ? trim((optional($v->RicePackingBuyer)->packing ?? '').' '.(optional($v->RicePackingBuyer)->description ?? ''))
                                                                : trim((string) (optional($v->RicePackingSeller)->description ?? ''));
                                                        }
                                                        if ($currentPackingLabel === '') {
                                                            $currentPackingLabel = optional($rowPackings->firstWhere('id', (int) $v->packing))->label ?? '--';
                                                        }
                                                    @endphp
                                                    <td data-order="{{ $currentPackingLabel }}">
                                                        <select class="form-control input-sm js-trade-packing" style="min-width:140px;max-width:180px;"
                                                                data-trade-id="{{ $v->id }}"
                                                                data-original="{{ $v->packing }}">
                                                            @foreach($rowPackings as $opt)
                                                                <option value="{{ $opt->id }}" {{ (string) $v->packing === (string) $opt->id ? 'selected' : '' }}>{{ $opt->label }}</option>
                                                            @endforeach
                                                            @if($v->packing && ! $rowPackings->contains('id', (int) $v->packing))
                                                                <option value="{{ $v->packing }}" selected>{{ $currentPackingLabel !== '' ? $currentPackingLabel : 'Current packing' }}</option>
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>{{ ($v->quantity )?? '--'}}</td>
                                                    <td>{{ ($v->offerPrice )?? '--'}}</td>
                                                    <td class="js-trade-valid-days">{{ ($v->validDays )?? '--'}}</td>
                                                    <td><div style="width: 100px;height: 100px"><img src="{{ asset('uploads/'.$v->packing_file) }}" style="width: 70px" /></div></td>
                                                    <td><div style="width: 100px;height: 100px"><img src="{{ asset('uploads/'.$v->uncooked_file) }}" style="width: 70px" /></div></td>
                                                    <td><div style="width: 100px;height: 100px"><img src="{{ asset('uploads/'.$v->cooked_file) }}" style="width: 70px" /></div></td>
                                                    <td>{{ App\TradeQueriesINR::$tradeStatus[$v->status] ?? 'Archived' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($v->created_at)->format('d-M-Y, g:i A') }}</td>


                                                    <td style="text-align: center;">
                                                        @if($v->status != 2 && $v->status != 3)
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' ,[ 'tradeid' => $v->id , 'status'=> 3]) }}">Sold</a>
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' , ['tradeid' => $v->id , 'status'=> 2]) }}">Expired</a>
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' , ['tradeid' => $v->id , 'status'=> 6]) }}">Active</a>
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' , ['tradeid' => $v->id , 'status'=> 11]) }}">close</a>
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' , ['tradeid' => $v->id , 'status'=> 12]) }}">Hold</a>
                                                        @endif

                                                        @if($v->status != 1)
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' ,[ 'tradeid' => $v->id , 'status'=> 1]) }}">Active</a>
                                                        @endif

                                                        @if($v->status != 4)
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' ,[ 'tradeid' => $v->id , 'status'=> 4]) }}">In-process</a>
                                                        @endif
                                                        
                                                        @if($v->status != 5)
                                                            <a class="btn btn-info btn-sm" href="{{ route('master.trade.change.status' ,[ 'tradeid' => $v->id , 'status'=> 5]) }}">De-Active</a>
                                                        @endif
                                                        <a href="{{ route('master.trade.edit' , $v->id) }}" class="btn btn-success">Edit</a>
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th style="text-align: center">SNTC Lot No</th>
                                                <th style="text-align: center">Trade ID</th>
                                                <th style="text-align: center">Trade Type</th>
                                                <th style="text-align: center">Quality Type</th>
                                                <th style="text-align: center">Quality</th>
                                                <th style="text-align: center">QualityForm</th>
                                                <th style="text-align: center">Link With Live Price</th>
                                                <th style="text-align: center">Link With Live Price state</th>
                                                <th style="text-align: center">Grade</th>
                                                <th style="text-align: center">Packing</th>
                                                <th style="text-align: center">Quantity</th>
                                                <th style="text-align: center">OfferPrice</th>
                                                <th style="text-align: center">ValidDays</th>
                                                <th style="text-align: center">Packing File</th>
                                                <th style="text-align: center">Uncooked File</th>
                                                <th style="text-align: center">Cooked File</th>
                                                <th style="text-align: center">Status</th>
                                                <th style="text-align: center">Created at</th>
                                                <th style="text-align: center">Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('scripts')
<script>
    function syncTradeBulkValidDaysUi() {
        var count = $('.js-trade-select:checked').length;
        $('#js-bulk-valid-days-count').text(count);
        $('#js-bulk-valid-days-selected').text(count);
        $('#js-bulk-valid-days-btn').prop('disabled', count < 1);
        var total = $('.js-trade-select').length;
        var allChecked = total > 0 && count === total;
        $('#js-trade-select-all').prop('checked', allChecked);
    }

    $(document).on('change', '.js-trade-select', function () {
        syncTradeBulkValidDaysUi();
    });

    $(document).on('change', '#js-trade-select-all', function () {
        var checked = $(this).is(':checked');
        $('.js-trade-select').prop('checked', checked);
        syncTradeBulkValidDaysUi();
    });

    $(document).on('click', '#js-bulk-valid-days-btn', function () {
        if ($('.js-trade-select:checked').length < 1) {
            return;
        }
        $('#bulkValidDaysModal').modal('show');
    });

    $(document).on('click', '#js-bulk-valid-days-submit', function () {
        var $btn = $(this);
        var ids = $('.js-trade-select:checked').map(function () {
            return parseInt($(this).val(), 10);
        }).get().filter(function (id) {
            return id > 0;
        });
        var validity = $('#bulkValidityInput').val();

        if (!ids.length) {
            alert('Please select at least one trade.');
            return;
        }
        if (!validity) {
            alert('Please choose a valid date.');
            return;
        }

        $btn.prop('disabled', true);
        $.ajax({
            url: window.route + '/trade/bulk-update-valid-days',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                trade_ids: ids,
                validity: validity
            },
            success: function (res) {
                var validDays = (res && res.validDays) ? res.validDays : validity;
                ids.forEach(function (id) {
                    $('tr[data-trade-id="' + id + '"]').find('.js-trade-valid-days').text(validDays);
                });
                $('.js-trade-select, #js-trade-select-all').prop('checked', false);
                syncTradeBulkValidDaysUi();
                $('#bulkValidDaysModal').modal('hide');
                if (typeof toastr !== 'undefined') {
                    toastr.success((res && res.message) ? res.message : 'Valid date updated.', 'Success');
                } else {
                    alert((res && res.message) ? res.message : 'Valid date updated.');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Could not update valid date.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var first = Object.values(xhr.responseJSON.errors)[0];
                    if (first && first[0]) {
                        msg = first[0];
                    }
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg, 'Error');
                } else {
                    alert(msg);
                }
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click','.js-trade-note',function(){
        var type = $(this).data('type');
        var label = type.charAt(0).toUpperCase() + type.slice(1);
        $('#tradeNoteText').text('This action will delete  '+ label +' records older than 30 days.');
        $('#purgeType').val(type);
        $('#tradeNoteModal').modal('show');
    });

    $(document).on('change', '.js-trade-packing', function () {
        var $select = $(this);
        var tradeId = $select.data('trade-id');
        var packingId = $select.val();
        var previous = $select.data('original');

        $select.prop('disabled', true);
        $.ajax({
            url: window.route + '/trade/update-packing/' + tradeId,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                packing: packingId
            },
            success: function (res) {
                $select.data('original', packingId);
                $select.closest('td').attr('data-order', $select.find('option:selected').text());
                if (typeof toastr !== 'undefined') {
                    toastr.success((res && res.message) ? res.message : 'Packing updated.', 'Success');
                }
            },
            error: function (xhr) {
                $select.val(previous);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not update packing.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg, 'Error');
                } else {
                    alert(msg);
                }
            },
            complete: function () {
                $select.prop('disabled', false);
            }
        });
    });
</script>
@endsection
