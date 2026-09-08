<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to SNTC</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #222; line-height: 1.5;">
    <p>Dear {{ $userName }},</p>

    <p>Welcome to <strong>SNTC</strong>.</p>

    <p>
        Thank you for registering with us
        @if(!empty($userEmail))
            ({{ $userEmail }})
        @endif
        . We are pleased to have you on board.
    </p>

    <p>
        Please find attached our <strong>Terms and Conditions</strong> PDF for your reference.
        We recommend reading it carefully.
    </p>

    <p>
        If you have any questions, contact us at
        <a href="mailto:enquiry@sntcgroup.com">enquiry@sntcgroup.com</a>.
    </p>

    <p>
        Regards,<br>
        <strong>SNTC Team - India</strong><br>
        <a href="mailto:info@sntcgroup.com">info@sntcgroup.com</a>
    </p>
</body>
</html>
