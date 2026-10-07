<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
  <div style="margin:50px auto;width:70%;padding:20px 0">
    <div style="border-bottom:1px solid #eee">
      <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">Greetings from SNTC!</a>
    </div>
    <p style="font-size:1.1em">Hi,</p>
    <p>You got a new <strong>Job Application</strong>.</p>

    <p style="margin-top:1em;"><strong>Vacancy details</strong></p>
    <p><strong>Job Title:</strong> {{ $job_title ?? '—' }}</p>
    <p><strong>Job Role:</strong> {{ $job_role ?? '—' }}</p>
    <p><strong>Location:</strong> {{ $job_location ?? '—' }}</p>
    <p><strong>Employment Type:</strong> {{ $job_type ?? '—' }}</p>
    <p><strong>Last Date to Apply:</strong> {{ $job_last_date ?? '—' }}</p>

    <p style="margin-top:1em;"><strong>Applicant details</strong></p>
    <p><strong>Application ID:</strong> {{ $application_id ?? '—' }}</p>
    <p><strong>Name:</strong> {{ $applicant_name ?? '—' }}</p>
    <p><strong>Email:</strong> {{ $applicant_email ?? '—' }}</p>
    <p><strong>Mobile:</strong> {{ $applicant_mobile ?? '—' }}</p>
    <p><strong>Experience:</strong> {{ $applicant_experience ?? '—' }}</p>
    @if(!empty($cv_url))
      <p><strong>CV:</strong> <a href="{{ $cv_url }}" target="_blank">View / Download CV</a> (also attached with this mail)</p>
    @endif
    <p><strong>Applied At:</strong> {{ $applied_at ?? '—' }}</p>

    <p style="margin-top:1em;">Thank you for your patience.</p>
    <br>
    <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
    <hr style="border:none;border-top:1px solid #eee" />
  </div>
</div>
