<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">
            <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
        </div>
        <p>Dear {{ $userName }},</p>
        <p>Your analyser account login details are below:</p>
        <p>Login URL: <strong>{{ $loginUrl }}</strong></p>
        <p>Email: <strong>{{ $email }}</strong></p>
        @if(!empty($password))
            <p>Password: <strong>{{ $password }}</strong></p>
        @endif
        <p>Login OTP (fallback): <strong>{{ $otp }}</strong></p>
        <p>Access Period: <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong></p>
        <p>Historical Data Access: <strong>{{ !empty($hasHistoricalAccess) ? 'Yes' : 'No' }}</strong></p>
        <p>Today Data Access: <strong>{{ !empty($hasTodayAccess) ? 'Yes' : 'No' }}</strong></p>
        <p>Please open the login URL and sign in with your email and password. You can also use the OTP if needed.</p>

        <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
        <hr style="border:none;border-top:1px solid #eee" />
    </div>
</div>
