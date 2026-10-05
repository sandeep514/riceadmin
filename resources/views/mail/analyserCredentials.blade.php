<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">
            <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
        </div>
        <p>Dear {{ $userName }},</p>
        <p>Your analyser account has been created. Please find your login details below:</p>
        <p>Login URL: <strong>{{ $loginUrl }}</strong></p>
        <p>Email: <strong>{{ $email }}</strong></p>
        <p>Login OTP: <strong>{{ $otp }}</strong></p>
        <p>Access Period: <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong></p>
        <p>Historical Data Access: <strong>{{ !empty($hasHistoricalAccess) ? 'Yes' : 'No' }}</strong></p>
        <p>Today Data Access: <strong>{{ !empty($hasTodayAccess) ? 'Yes' : 'No' }}</strong></p>
        <p>Please open the login URL, enter your email and use the OTP above to log in. No password is required.</p>

        <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
        <hr style="border:none;border-top:1px solid #eee" />
    </div>
</div>
