<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">
            <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
        </div>

        <p style="font-size:1.1em">Hi,</p>
        <p>
            A new <strong>brand interest</strong> has been submitted on the SNTC web portal.
            Please review and follow up with the contact person.
        </p>

        <p><strong>Brand</strong><br>
            Brand: {{ $data['brandName'] ?? '—' }}<br>
            Brand ID: {{ $data['brandId'] ?? '—' }}<br>
            Interest ID: {{ $data['interestId'] ?? '—' }}
        </p>

        <p><strong>Contact person</strong><br>
            Name: {{ $data['contactPersonName'] ?? '—' }}<br>
            Phone: {{ $data['contactPersonNumber'] ?? '—' }}
        </p>

        <p><strong>Business details</strong><br>
            Already working with brands: {{ $data['workingWithBrand'] ?? '—' }}<br>
            Other brands (if any): {{ $data['brandNames'] ?? '—' }}<br>
            Basmati monthly volume: {{ $data['basmatiMonthly'] ?? '—' }}<br>
            Non-Basmati monthly volume: {{ $data['nonBasmatiMonthly'] ?? '—' }}
        </p>

        <p><strong>Interested locations</strong><br>
            {{ $data['interestedLocations'] ?? '—' }}
        </p>

        <p><strong>Submitted by</strong><br>
            User ID: {{ $data['userId'] ?? '—' }}<br>
            @if(!empty($data['userName']))
                User name: {{ $data['userName'] }}<br>
            @endif
            @if(!empty($data['userEmail']))
                User email: {{ $data['userEmail'] }}<br>
            @endif
            Date &amp; time: {{ $data['submittedAt'] ?? '—' }}
        </p>

        <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
        <hr style="border:none;border-top:1px solid #eee" />
    </div>
</div>
