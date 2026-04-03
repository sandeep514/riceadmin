@extends('layouts.main')

@section('content')

<div class="content-wrapper">
	<div class="container">
		<h2>User Details - ID #{{ $user['id'] }}</h2>

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
							$gstFssaiRel = $user['get_web_user_attachment']['gst_fssai'] ?? null;
						@endphp
						@if (!empty($gstFssaiRel))
							@php
								$gstFssaiUrl = asset('webPortal/' . $user['id'] . '/attachments/' . $gstFssaiRel);
								$basename = basename($gstFssaiRel);
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
	
});
</script>
@endsection
