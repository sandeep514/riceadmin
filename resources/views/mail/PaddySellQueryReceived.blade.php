<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
  <div style="margin:50px auto;width:70%;padding:20px 0">
    <div style="border-bottom:1px solid #eee">
      <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">Greetings from SNTC!</a>
    </div>
    <p style="font-size:1.1em">Hi,</p>
    <p>You got a new <strong>Paddy Sell Query</strong>.</p>

    <p style="margin-top:1em;"><strong>Submitted by (account)</strong></p>
    <p><strong>Name:</strong> {{ $creator_name ?? '—' }}</p>
    <p><strong>Email:</strong> {{ $creator_email ?? '—' }}</p>
    <p><strong>Phone:</strong> {{ $creator_phone ?? '—' }}</p>

    <p style="margin-top:1em;"><strong>Paddy sell query details</strong></p>
    <p><strong>Query ID:</strong> {{ $query_id ?? '—' }}</p>
    <p><strong>Category:</strong> {{ $category_label ?? ($category ?? '—') }}</p>
    <p><strong>Quality:</strong> {{ $quality_name ?? '—' }}</p>
    <p><strong>Hand / Combined:</strong> {{ $hand_combined ?? '—' }}</p>
    <p><strong>Packing:</strong> {{ $packing ?? '—' }}</p>
    <p><strong>Quantity:</strong> {{ $quantity ?? '—' }}</p>
    <p><strong>Rate:</strong> {{ $rate ?? '—' }}</p>
    <p><strong>Valid Days:</strong> {{ $valid_days ?? '—' }}</p>
    <p><strong>Location:</strong> {{ $location ?? '—' }}</p>
    <p><strong>Contact Person:</strong> {{ $contact_person ?? '—' }}</p>
    <p><strong>Contact Number:</strong> {{ $contact_number ?? '—' }}</p>
    <p><strong>Type:</strong> {{ $type ?? '—' }}</p>
    @if(!empty($image_url))
      <p><strong>Image:</strong> <a href="{{ $image_url }}" target="_blank">View image</a></p>
    @endif

    <p style="margin-top:1em;">Thank you for your patience.</p>
    <br>
    <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
    <hr style="border:none;border-top:1px solid #eee" />
  </div>
</div>
