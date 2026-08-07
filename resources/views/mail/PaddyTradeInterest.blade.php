<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
  <div style="margin:50px auto;width:70%;padding:20px 0">
    <div style="border-bottom:1px solid #eee">
      <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing</a>
    </div>

    <p style="font-size:1.1em">Hi,</p>
    <p>Dear Team, a user showed interest on a <strong>Paddy Trade</strong>.</p>

    <p style="margin-top:1em;"><strong>User details</strong></p>
    <ul>
      <li><strong>Name:</strong> {{ $username ?? '—' }}</li>
      <li><strong>Email:</strong> {{ $email ?? '—' }}</li>
      <li><strong>Mobile:</strong> {{ $mobile ?? '—' }}</li>
      <li><strong>Company:</strong> {{ $companyName ?? '—' }}</li>
      <li><strong>User ID:</strong> {{ $userId ?? '—' }}</li>
    </ul>

    <p style="margin-top:1em;"><strong>Paddy trade details</strong></p>
    <ul>
      <li><strong>Trade ID:</strong> PaddyTrade_{{ $tradeId ?? '—' }}</li>
      <li><strong>Category:</strong> {{ $category_label ?? ($category ?? '—') }}</li>
      <li><strong>Quality:</strong> {{ $qualityName ?? '—' }}</li>
      <li><strong>Hand / Combined:</strong> {{ $hand_combined ?? '—' }}</li>
      <li><strong>Packing:</strong> {{ $packing ?? '—' }}</li>
      <li><strong>Quantity:</strong> {{ $quantity ?? '—' }}</li>
      <li><strong>Rate:</strong> {{ $rate ?? '—' }}</li>
      <li><strong>Valid Days:</strong> {{ $validDays ?? '—' }}</li>
      <li><strong>Location:</strong> {{ $location ?? '—' }}</li>
      <li><strong>Contact Person:</strong> {{ $contactperson ?? '—' }}</li>
      <li><strong>Contact Number:</strong> {{ $contactNumber ?? '—' }}</li>
      @if(!empty($imageUrl))
        <li><strong>Image:</strong> <a href="{{ $imageUrl }}" target="_blank">View image</a></li>
      @endif
    </ul>

    <br>
    <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
    <hr style="border:none;border-top:1px solid #eee" />
  </div>
</div>
