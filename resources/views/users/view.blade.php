@extends('layouts.main')

@section('content')

<div class="content-wrapper">
	<div class="container">
		<h2>User Details - ID #{{ $user['id'] }}</h2>

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
					<th>Status</th>
					<td>
						@if(($user['status'] ?? 0) == 1)
							<span class="status-active">Active</span>
						@else
							<span class="status-inactive">Inactive</span>
						@endif
					</td>
				</tr>
				<tr><th>INR Active</th><td>{{ ($user['is_INR_active'] ?? 0) ? 'Yes' : 'No' }}</td></tr>
				<tr><th>USD Active</th><td>{{ ($user['is_usd_active'] ?? 0) ? 'Yes' : 'No' }}</td></tr>
				<tr><th>Created At</th><td>{{ $user['created_at'] ?? '-' }}</td></tr>
				<tr><th>Updated At</th><td>{{ $user['updated_at'] ?? '-' }}</td></tr>
			</table>
		</div>

		{{-- Rice / portal interests (user_interested_map_table) --}}
		<div class="section">
			<h3>Rice interests</h3>
			<p class="text-muted">Saved preferences used by the web portal. Incomplete lines are ignored when saving. To clear everything, remove all lines with the Remove button and click <strong>Save interests</strong>.</p>

			@if (isset($interestedMaps) && $interestedMaps->isNotEmpty())
				<div class="table-responsive" style="margin-bottom:16px;">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>Rice name</th>
								<th>Form</th>
								<th>Wand / grade</th>
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
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@else
				<p class="text-muted">No interests saved yet.</p>
			@endif

			<form method="POST" action="{{ route('save.user.interests', $user['id']) }}">
				@csrf
				<h4 style="margin-top:20px;">Edit interests</h4>
				<div id="interest-rows">
					@foreach ($interestEditRows ?? [] as $idx => $row)
						@include('users.interest_row', ['idx' => $idx, 'row' => $row])
					@endforeach
				</div>
				<p style="margin-top:10px;">
					<button type="button" class="btn btn-default btn-sm" id="add-interest-row">Add line</button>
					<button type="submit" class="btn btn-primary btn-sm">Save interests</button>
				</p>
			</form>

			<div id="interest-row-skeleton" style="display:none;">
				@include('users.interest_row', ['idx' => '__IDX__', 'row' => ['rice_type' => '', 'name_id' => '', 'form_id' => '', 'grades' => []]])
			</div>
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
		<div class="section">
			<h3>Business Details</h3>
			<table class="table">
				<tr><th>Company Name</th><td>{{ $user['get_web_business_details']['company_name'] ?? '-' }}</td></tr>
				<tr><th>Selected Category</th><td>{{ $user['get_web_business_details']['get_category_details']['category'] ?? ($user['get_web_business_details']['selected_category'] ?? '-') }}</td></tr>
				<tr><th>Address</th><td>{{ $user['get_web_business_details']['address'] ?? '-' }}</td></tr>
				<tr><th>City</th><td>{{ $user['get_web_business_details']['city_rel']['city_name'] ?? '-' }}</td></tr>
				<tr><th>State</th><td>{{ $user['get_web_business_details']['state_rel']['state_name'] ?? '-' }}</td></tr>
			</table>
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
							@if( $user['is_active_by_admin'] == 0 )
							<div>
								<div>
									<a href="{{ route('list.web.change.status.user' , $user['id']) }}" class="btn btn-sm btn-info">
										Activate this user
									</a>
								</div>
								
							</div>
							@else
								<a href="{{ route('list.web.change.status.user' , $user['id']) }}" class="btn btn-sm btn-danger">De-Activate this user</a>
							@endif
							<div class="row">
								<div class="col-md-6">
									<form class="" method="POST" action="{{ route('reject.user') }}">
										<input type="hidden" name="userId" value="{{ $user['id'] }}">
										@csrf
										<div class="form-group">
										    <label for="message">Reason of Rejection:</label>
										    <input type="text" class="form-control" id="message" name="message">
										</div>
										<div>
											<input type="submit" class="btn btn-info btn-sm" name="submit" value="submit">	
										</div>
									</form>									
								</div>
							</div>
						</div>
					</div>
				@endif
			@endif

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
});
</script>
@endsection
