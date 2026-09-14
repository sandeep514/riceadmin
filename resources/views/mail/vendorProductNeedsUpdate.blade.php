<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">
            <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
        </div>

        <p style="font-size:1.1em">Dear {{ $data['userName'] ?? 'Vendor' }},</p>
        <p>
            The SNTC team needs an update on your
            <strong>{{ $data['productKind'] ?? 'vendor' }}</strong> product before it can stay visible on the platform.
        </p>

        <p><strong>Product details</strong><br>
            Product: {{ $data['productKind'] ?? '—' }}<br>
            Product ID: {{ $data['productId'] ?? '—' }}<br>
            Type: {{ $data['typeLabel'] ?? '—' }}<br>
            @if(!empty($data['companyName']))
                Company: {{ $data['companyName'] }}<br>
            @endif
            Date &amp; time: {{ $data['sentAt'] ?? '—' }}
        </p>

        <p><strong>Message from admin</strong><br>
            {{ $data['message'] ?? '—' }}
        </p>

        <p>
            Please review this product in your account and make the required changes, then contact us if you need help at
            <a href="mailto:enquiry@sntcgroup.com">enquiry@sntcgroup.com</a>.
        </p>

        <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
        <hr style="border:none;border-top:1px solid #eee" />
    </div>
</div>
