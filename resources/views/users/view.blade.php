@extends('layouts.main')

@section('content')

<div class="content-wrapper">
	<div class="container">
		<h2>User Details - ID #{{ $user['id'] }}</h2>
		@if (($user['user_from'] ?? '') === 'web')
			<div style="margin: 10px 0 20px 0;">
				<form id="delete-web-user-form" method="POST" action="{{ route('delete.web.user.with.pin', $user['id']) }}" style="display:inline;">
					@csrf
					<input type="hidden" name="pin" id="delete-web-user-pin" value="">
					<button type="button" class="btn btn-danger btn-sm" id="delete-web-user-btn">
						<i class="fa fa-trash"></i> Delete Web User
					</button>
				</form>
			</div>
		@endif

		@if ($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0" style="padding-left:18px;">
					@foreach ($errors->all() as $err)
						<li>{{ $err }}</li>
					@endforeach
				</ul>
			</div>
		@endif
		@if (session('error'))
			@php $parts = explode('|', session('error'), 2); @endphp
			<div class="alert alert-danger">{{ $parts[1] ?? $parts[0] }}</div>
		@endif
		@if (session('success'))
			@php $parts = explode('|', session('success'), 2); @endphp
			<div class="alert alert-success">{{ $parts[1] ?? $parts[0] }}</div>
		@endif

		<!-- Basic Info -->
		<div class="section">
			<h3>Basic Information</h3>
			<table class="table">
				<tr><th>Name</th><td>{{ $user['name'] ?? '-' }}</td></tr>
				<tr><th>Email</th><td>{{ $user['email'] ?? '-' }}</td></tr>
				<tr><th>Mobile</th><td>{{ $user['mobile'] ?? '-' }}</td></tr>
				<tr><th>Role</th><td>{{ $user['role_rel']['role_name'] ?? ($user['role'] ?? '-') }}</td></tr>
				<tr>
					<th>Account Status</th>
					<td>
						@if((int) ($user['is_deactivated'] ?? 0) === 1)
							<span class="status-inactive">Deactivated</span>
						@elseif(($user['is_active_by_admin'] ?? 0) == 1)
							<span class="status-active">Active</span>
						@else
							<span class="status-inactive">Pending Activation</span>
						@endif
					</td>
				</tr>
				<tr>
					<th>Verification Status</th>
					<td>
						@if(($user['status'] ?? 0) == 1)
							<span class="status-active">Verified</span>
						@else
							<span class="status-inactive">Unverified</span>
						@endif
					</td>
				</tr>
				<tr><th>INR Active</th><td>{{ ($user['is_INR_active'] ?? 0) ? 'Yes' : 'No' }}</td></tr>
				<tr><th>USD Active</th><td>{{ ($user['is_usd_active'] ?? 0) ? 'Yes' : 'No' }}</td></tr>
				<tr><th>Created At</th><td>{{ $user['created_at'] ?? '-' }}</td></tr>
				<tr><th>Updated At</th><td>{{ $user['updated_at'] ?? '-' }}</td></tr>
				<tr><th>Can edit by admin</th><td>{{ ($canEditByAdmin ?? 0) === 1 ? 'Yes (1)' : 'No (0)' }}</td></tr>
			</table>
		</div>

		{{-- Rice / portal interests (user_interested_map_table) --}}
		<div class="section">
			<h3>Rice interests</h3>
			<p class="text-muted" style="margin-bottom:12px;">
				<strong>Search experience preference:</strong>
				@if (($canEditByAdmin ?? 0) === 1)
					Let SNTC approve my search experience (admin may manage interests).
				@else
					I will do it myself (user manages interests on the portal).
				@endif
			</p>
			@if ($canAdminManageInterests === true)
				<p class="text-muted">Saved preferences used by the web portal. Use <strong>Delete</strong> in the table to remove a saved row. The form below <strong>adds</strong> new rice + form + wand combinations; anything already saved is kept (duplicates are skipped). To change wands for an existing rice + form, delete those rows in the table first, then add the new combination here.</p>
			@else
				<p class="text-muted">Read-only: this user chose to manage their own search experience. Interests are shown below for reference only.</p>
			@endif

			@if (isset($interestedMaps) && $interestedMaps->isNotEmpty())
				<div class="table-responsive" style="margin-bottom:16px;">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>Rice name</th>
								<th>Form</th>
								<th>Wand / grade</th>
								@if ($canAdminManageInterests === true)
									<th style="width:100px;">Actions</th>
								@endif
							</tr>
						</thead>
						<tbody>
							@foreach ($interestedMaps as $im)
								<tr>
									<td>{{ optional($im->riceName)->name ?? '—' }}</td>
									<td>{{ optional($im->riceForm)->name ?? '—' }}</td>
									<td>
										@if ($im->grade && $im->wandGrade)
											@if ($im->wandGrade->getWandType)
												{{ $im->wandGrade->getWandType->type }} — {{ $im->wandGrade->value }}
											@else
												{{ $im->wandGrade->value }}
											@endif
										@elseif ($im->grade)
											#{{ $im->grade }}
										@else
											<span class="text-muted">All / not set</span>
										@endif
									</td>
									@if ($canAdminManageInterests === true)
										<td>
											<form method="POST" action="{{ route('delete.user.interest.row', $user['id']) }}" style="display:inline;" onsubmit="return confirm('Delete this interest row?');">
												@csrf
												<input type="hidden" name="map_id" value="{{ $im->id }}">
												<button type="submit" class="btn btn-danger btn-xs">Delete</button>
											</form>
										</td>
									@endif
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@else
				<p class="text-muted">No interests saved yet.</p>
			@endif

			@if ($canAdminManageInterests === true)
			<form method="POST" action="{{ route('save.user.interests', $user['id']) }}">
				@csrf
				<h4 style="margin-top:20px;">Add more interests</h4>
				<p class="text-muted small">Use <strong>Add line</strong> for another rice (e.g. 1401) without losing what is already saved (e.g. 1121). <strong>Save</strong> only inserts new rows.</p>
				<div id="interest-rows">
					@foreach ($interestEditRows ?? [] as $idx => $row)
						@include('users.interest_row', ['idx' => $idx, 'row' => $row])
					@endforeach
				</div>
				<p style="margin-top:10px;">
					<button type="button" class="btn btn-default btn-sm" id="add-interest-row">Add line</button>
					<button type="submit" class="btn btn-primary btn-sm">Save new interests</button>
				</p>
			</form>

			<div id="interest-row-skeleton" style="display:none;">
				@include('users.interest_row', ['idx' => '__IDX__', 'row' => ['rice_type' => '', 'name_id' => '', 'form_id' => '', 'grades' => []]])
			</div>
			@endif
		</div>

		<!-- Personal Info -->
		@if (!empty($user['get_web_personal_details']))
		<div class="section">
			<h3>Personal Details</h3>
			<table class="table">
				<tr><th>First Name</th><td>{{ $user['get_web_personal_details']['firstname'] ?? '-' }}</td></tr>
				<tr><th>Last Name</th><td>{{ $user['get_web_personal_details']['lastname'] ?? '-' }}</td></tr>
				<tr><th>Email</th><td>{{ $user['get_web_personal_details']['email'] ?? '-' }}</td></tr>
				<tr><th>Phone Number</th><td>{{ $user['get_web_personal_details']['phone_number'] ?? '-' }}</td></tr>
			<!-- 	<tr><th>State</th><td>{{ $user['get_web_personal_details']['state'] ?? '-' }}</td></tr>
				<tr><th>District</th><td>{{ $user['get_web_personal_details']['district'] ?? '-' }}</td></tr>
				<tr><th>Address</th><td>{{ $user['get_web_personal_details']['address'] ?? '-' }}</td></tr> -->

				{{-- PAN Card --}}
				<tr>
					<th>PAN Card</th>
					<td>
						@if (!empty($user['get_web_user_attachment']['panCard']))
							@php
								$filename = $user['get_web_user_attachment']['panCard'];
							@endphp

							@if (preg_match('/\.(png|jpe?g)$/i', $filename))
								<img 
									src="{{ asset('webPortal/' . $user['id'] . '/attachments/pan/' . $user['get_web_user_attachment']['panCard']) }}" 
									alt="PAN Card" 
									width="120"
									class="img-thumbnail"
									style="cursor:pointer"
									data-bs-toggle="modal" 
									data-bs-target="#imageModal" 
									data-image="{{ asset('webPortal/' . $user['id'] . '/attachments/pan/' . $user['get_web_user_attachment']['panCard']) }}"
								>
							@else
								<a href="{{ asset('webPortal/' . $user['id'] . '/attachments/pan/' . $user['get_web_user_attachment']['panCard']) }}">View</a>
							@endif
						@else
							--
						@endif
					</td>
				</tr>

				{{-- GST or FSSAI (single document) --}}
				<tr>
					<th>GST / FSSAI</th>
					<td>
						@php
							$gstFssaiFile = $user['get_web_user_attachment']['gst_fssai'] ?? null;
							$gstFssaiFolder = $user['get_web_user_attachment']['gst_fssai_folder'] ?? 'gst_fssai';
						@endphp
						@if (!empty($gstFssaiFile))
							@php
								$gstFssaiUrl = asset('webPortal/' . $user['id'] . '/attachments/' . $gstFssaiFolder . '/' . $gstFssaiFile);
								$basename = $gstFssaiFile;
							@endphp
							@if (preg_match('/\.(png|jpe?g)$/i', $basename))
								<img
									src="{{ $gstFssaiUrl }}"
									alt="GST / FSSAI"
									width="120"
									class="img-thumbnail"
									style="cursor:pointer"
									data-bs-toggle="modal"
									data-bs-target="#imageModal"
									data-image="{{ $gstFssaiUrl }}"
								>
							@else
								<a href="{{ $gstFssaiUrl }}" target="_blank">View</a>
							@endif
						@else
							--
						@endif
					</td>
				</tr>
			</table>
		</div>


			
		@endif

		<!-- Business Info -->
		@if (!empty($user['get_web_business_details']))
		@php
			$businessDetails = $user['get_web_business_details'];
		@endphp
		<div class="section">
			<h3>Business Details</h3>
			<table class="table">
				<tr><th>Company Name</th><td>{{ $businessDetails['company_name'] ?? '-' }}</td></tr>
				<tr><th>Product</th><td>{{ $businessDetails['product'] ?? '-' }}</td></tr>
				<tr><th>Contact Name</th><td>{{ $businessDetails['contactPerson'] ?? '-' }}</td></tr>
				<tr><th>Contact Mobile</th><td>{{ $businessDetails['contactMobile'] ?? '-' }}</td></tr>
				<tr><th>Selected Category</th><td>{{ $businessDetails['get_category_details']['category'] ?? ($businessDetails['selected_category'] ?? '-') }}</td></tr>
				<tr><th>Address</th><td>{{ $businessDetails['address'] ?? '-' }}</td></tr>
				<tr><th>City</th><td>{{ $businessDetails['city_rel']['city_name'] ?? '-' }}</td></tr>
				<tr><th>State</th><td>{{ $businessDetails['state_rel']['state_name'] ?? '-' }}</td></tr>
			</table>

			<div class="row" style="margin-top: 16px;">
				<div class="col-md-6">
					<div class="box box-primary">
						<div class="box-header with-border">
							<h3 class="box-title">SNTC Recommended</h3>
						</div>
						<div class="box-body">
							<form method="POST" action="{{ route('update.user.business.details.recommended', $user['id']) }}">
								@csrf
								<input type="hidden" name="update_field" value="is_sntc_recommended">
								<input type="hidden" name="is_sntc_recommended" value="0">
								<div class="checkbox" style="margin-top:0;">
									<label>
										<input
											type="checkbox"
											name="is_sntc_recommended"
											value="1"
											{{ ((int) ($businessDetails['is_sntc_recommended'] ?? 0) === 1) ? 'checked' : '' }}
										>
										Mark this business as SNTC Recommended
									</label>
								</div>
								<button type="submit" class="btn btn-primary btn-sm">Save</button>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="box box-success">
						<div class="box-header with-border">
							<h3 class="box-title">Active Listing</h3>
						</div>
						<div class="box-body">
							<form method="POST" action="{{ route('update.user.business.details.recommended', $user['id']) }}">
								@csrf
								<input type="hidden" name="update_field" value="is_active_listing">
								<input type="hidden" name="is_active_listing" value="0">
								<div class="checkbox" style="margin-top:0;">
									<label>
										<input
											type="checkbox"
											name="is_active_listing"
											value="1"
											{{ ((int) ($businessDetails['is_active_listing'] ?? 0) === 1) ? 'checked' : '' }}
										>
										Show this business in active listings
									</label>
								</div>
								<button type="submit" class="btn btn-success btn-sm">Save</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		@endif

		@if (!empty($vendorProfileType))
		<div class="section">
			<h3>{{ $vendorProfileType === 'vendor' ? 'Vendor User Map' : 'Service Provider User Map' }}</h3>
			@if (isset($vendorProfileMaps) && $vendorProfileMaps->isNotEmpty())
				<div class="table-responsive">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>Vendor Type</th>
								<th>Packing Type</th>
								<th>Specialisation</th>
								<th>Remarks</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($vendorProfileMaps as $profileMap)
								<tr>
									<td>{{ $profileMap->type ?? '—' }}</td>
									<td>{{ $profileMap->key ?? '—' }}</td>
									<td>{{ $profileMap->value ?? '—' }}</td>
									<td>{{ $profileMap->remarks ?? '—' }}</td>
									<td>{{ ((int) ($profileMap->status ?? 0) === 1) ? 'Active' : 'Inactive' }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@else
				<p class="text-muted">No {{ $vendorProfileType === 'vendor' ? 'vendor user map' : 'service provider user map' }} records found.</p>
			@endif
		</div>
		@endif

		@if( array_key_exists('get_web_user_subscription' , $user ) )
				@if( $user['get_web_user_subscription'] == null )
					<div style="background: rgba(255, 0, 0, 0.5);opacity: 0.5;padding: 20px 10px;border: 2px solid red;border-radius: 12px;">
						<div style="text-align: center;">
							<p style="color: #000;padding: 0px;margin: 0px;font-size: 22px;">This person has no any Subscription , Trial plan.</p>
						</div>
					</div>
				@else
					<div>
						<div>
							@if( (int) ($user['is_active_by_admin'] ?? 0) === 0 )
							<div>
								<div>
									<a href="{{ route('list.web.change.status.user' , $user['id']) }}" class="btn btn-sm btn-info">
										Activate this user
									</a>
								</div>
								
							</div>
							@elseif( (int) ($user['is_deactivated'] ?? 0) === 1 )
								<a href="{{ route('list.web.change.status.user' , $user['id']) }}" class="btn btn-sm btn-info">Re-Activate this user</a>
							@else
								<a href="{{ route('list.web.change.status.user' , $user['id']) }}" class="btn btn-sm btn-danger">De-Activate this user</a>
							@endif
						</div>
					</div>
				@endif
			@endif
			<div class="section" style="margin-top: 20px;">
				<div class="box box-danger">
					<div class="box-header with-border">
						<h3 class="box-title">Reason of Rejection</h3>
					</div>
					<div class="box-body">
						<form method="POST" action="{{ route('reject.user') }}" id="reject-user-form">
							<input type="hidden" name="userId" value="{{ $user['id'] }}">
							@csrf
							<div class="form-group @error('message') has-error @enderror">
								<label for="reject-message">Reason of Rejection: <span class="text-danger">*</span></label>
								<textarea
									class="form-control"
									id="reject-message"
									name="message"
									rows="4"
									required
									placeholder="Enter the reason for rejecting this user"
								>{{ old('message') }}</textarea>
								@error('message')
									<span class="help-block text-danger">{{ $message }}</span>
								@enderror
							</div>
							<div>
								<button type="submit" class="btn btn-danger btn-sm">Reject User</button>
							</div>
						</form>
					</div>
				</div>
			</div>


	<!-- Trigger the modal with a button -->
<!-- <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Open Modal</button> -->

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div>
      <div class="modal-body">
        <p>Some text in the modal.</p>
        <img src="" class="modalBodyImage" id="modalBodyImage" style="width: 100%">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
	</div>

</div>


@endsection

@section('javascript')
<script>
$(document).ready(function() {
	$('.img-thumbnail').click(function (){
		let imageUrl = $(this).attr('src');

		$('#modalBodyImage').attr('src', imageUrl)
		$('#myModal').modal('show')
		// $('#imageModal').removeClass('fade')

	});

	$('#delete-web-user-btn').on('click', function () {
		if (typeof toastr !== 'undefined') {
			toastr.warning('This action will permanently delete this web user. Enter PIN to continue.', 'Confirm delete');
		}

		var confirmed = window.confirm('Are you sure you want to delete this web user?');
		if (!confirmed) return;

		var pin = window.prompt('Enter security PIN to confirm delete:');
		if (pin === null) return;

		pin = String(pin).trim();
		if (pin !== '22334455') {
			if (typeof toastr !== 'undefined') {
				toastr.error('Invalid PIN. User was not deleted.', 'Error');
			} else {
				alert('Invalid PIN. User was not deleted.');
			}
			return;
		}

		$('#delete-web-user-pin').val(pin);
		$('#delete-web-user-form').trigger('submit');
	});

	@if ($canAdminManageInterests === true)
	// --- Admin: rice interests (cascading selects) ---
	var interestRowCounter = $('#interest-rows .interest-row').length;

	function hydrateInterestRow($row) {
		var type = $row.attr('data-initial-type');
		if (!type) return;
		var nid = String($row.attr('data-initial-name-id') || '');
		var fid = String($row.attr('data-initial-form-id') || '');
		var grades = $row.data('initial-grades');
		if (!$.isArray(grades)) grades = [];

		$row.find('.js-interest-rice-type').val(type);
		var $name = $row.find('.js-interest-rice-name');
		$.get(window.route + '/rice-form-map/ajax/rice-names/' + encodeURIComponent(type), function (data) {
			$name.empty().append('<option value="">— Select —</option>');
			$.each(data, function (id, label) {
				$name.append($('<option>').val(id).text(label));
			});
			if (nid) $name.val(nid);
			if (!nid) return;
			$.get(window.route + '/user-interests/ajax/forms', { rice_name_id: nid, rice_type: type }, function (forms) {
				var $form = $row.find('.js-interest-form');
				$form.empty().append('<option value="">— Select —</option>');
				$.each(forms, function (id, label) {
					$form.append($('<option>').val(id).text(label));
				});
				if (fid) $form.val(fid);
				if (!fid) return;
				$.get(window.route + '/user-interests/ajax/wands', { rice_name_id: nid, form_id: fid }, function (wands) {
					var $w = $row.find('.js-interest-wands');
					$w.empty();
					$.each(wands, function (id, label) {
						$w.append($('<option>').val(id).text(label));
					});
					if (grades.length) {
						$w.val($.map(grades, String));
					}
				});
			});
		});
	}

	$('#interest-rows .interest-row').each(function () {
		hydrateInterestRow($(this));
	});

	$(document).on('change', '#interest-rows .js-interest-rice-type', function () {
		var $row = $(this).closest('.interest-row');
		var type = $(this).val();
		var $name = $row.find('.js-interest-rice-name');
		$name.empty().append('<option value="">— Select —</option>');
		$row.find('.js-interest-form').empty().append('<option value="">— Select —</option>');
		$row.find('.js-interest-wands').empty();
		if (!type) return;
		$.get(window.route + '/rice-form-map/ajax/rice-names/' + encodeURIComponent(type), function (data) {
			$.each(data, function (id, label) {
				$name.append($('<option>').val(id).text(label));
			});
		});
	});

	$(document).on('change', '#interest-rows .js-interest-rice-name', function () {
		var $row = $(this).closest('.interest-row');
		var rid = $(this).val();
		var type = $row.find('.js-interest-rice-type').val();
		var $form = $row.find('.js-interest-form');
		$form.empty().append('<option value="">— Select —</option>');
		$row.find('.js-interest-wands').empty();
		if (!rid) return;
		$.get(window.route + '/user-interests/ajax/forms', { rice_name_id: rid, rice_type: type || '' }, function (forms) {
			$.each(forms, function (id, label) {
				$form.append($('<option>').val(id).text(label));
			});
		});
	});

	$(document).on('change', '#interest-rows .js-interest-form', function () {
		var $row = $(this).closest('.interest-row');
		var rid = $row.find('.js-interest-rice-name').val();
		var fid = $(this).val();
		var $w = $row.find('.js-interest-wands');
		$w.empty();
		if (!rid || !fid) return;
		$.get(window.route + '/user-interests/ajax/wands', { rice_name_id: rid, form_id: fid }, function (wands) {
			$.each(wands, function (id, label) {
				$w.append($('<option>').val(id).text(label));
			});
		});
	});

	$('#add-interest-row').on('click', function () {
		var idx = 'n' + (++interestRowCounter) + '_' + Date.now();
		var html = $('#interest-row-skeleton').html().replace(/__IDX__/g, idx);
		var $new = $(html);
		$new.attr('data-initial-type', '').attr('data-initial-name-id', '').attr('data-initial-form-id', '').attr('data-initial-grades', '[]');
		$('#interest-rows').append($new);
	});

	$(document).on('click', '#interest-rows .js-remove-interest-row', function () {
		$(this).closest('.interest-row').remove();
	});
	@endif
});
</script>
@endsection
