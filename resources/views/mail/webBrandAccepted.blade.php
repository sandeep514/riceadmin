<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">
            <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
        </div>

        <p style="font-size:1.1em">Dear {{ $data['userName'] ?? 'User' }},</p>
        <p>
            Good news! Your brand request
            <strong>{{ $data['brandName'] ?? '—' }}</strong>
            has been <strong>accepted</strong> by the SNTC admin and is now live on the portal.
        </p>

        <p><strong>Brand details</strong><br>
            Brand ID: {{ $data['brandId'] ?? '—' }}<br>
            Name: {{ $data['brandName'] ?? '—' }}<br>
            Quality: {{ $data['qualityName'] ?? '—' }}<br>
            Accepted on: {{ $data['acceptedAt'] ?? '—' }}
        </p>

        <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
        <hr style="border:none;border-top:1px solid #eee" />
    </div>
</div>
