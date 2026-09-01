<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">
            <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
        </div>

        <p style="font-size:1.1em">Hi,</p>
        <p>
            @if(!empty($data['isCreate']))
                A vendor has submitted a new <strong>{{ $data['productKind'] ?? 'vendor' }}</strong> product with variants.
            @else
                A vendor has added new variants to an existing <strong>{{ $data['productKind'] ?? 'vendor' }}</strong> product.
            @endif
            Please review it from the admin panel.
        </p>

        <p><strong>Product details</strong><br>
            Product: {{ $data['productKind'] ?? '—' }}<br>
            Product ID: {{ $data['productId'] ?? '—' }}<br>
            Type: {{ $data['typeLabel'] ?? '—' }}<br>
            Status: {{ $data['statusLabel'] ?? 'Pending' }}<br>
            @if(!empty($data['specification']))
                Specification: {{ $data['specification'] }}<br>
            @endif
            @if(!empty($data['description']))
                Description: {{ $data['description'] }}<br>
            @endif
            Variants: {{ $data['variantCount'] ?? 0 }}
        </p>

        @if(!empty($data['variants']) && is_array($data['variants']))
            <p><strong>Variants</strong><br>
                @foreach($data['variants'] as $index => $variant)
                    {{ $index + 1 }}. {{ $variant }}<br>
                @endforeach
            </p>
        @endif

        <p><strong>Submitted by</strong><br>
            User ID: {{ $data['userId'] ?? '—' }}<br>
            @if(!empty($data['companyName']))
                Company: {{ $data['companyName'] }}<br>
            @endif
            @if(!empty($data['userName']))
                User name: {{ $data['userName'] }}<br>
            @endif
            @if(!empty($data['userEmail']))
                User email: {{ $data['userEmail'] }}<br>
            @endif
            @if(!empty($data['userMobile']))
                Mobile: {{ $data['userMobile'] }}<br>
            @endif
            Date &amp; time: {{ $data['submittedAt'] ?? '—' }}
        </p>

        @if(!empty($data['reviewUrl']))
            <p>
                <a href="{{ $data['reviewUrl'] }}">Review in admin panel</a>
            </p>
        @endif

        <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
        <hr style="border:none;border-top:1px solid #eee" />
    </div>
</div>
