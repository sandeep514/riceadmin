<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Deactivated - SNTC</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Arial, sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
    }
    .email-wrapper {
      max-width: 600px;
      margin: 40px auto;
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      overflow: hidden;
    }
    .header {
      background-color: #b91c1c;
      color: #ffffff;
      text-align: center;
      padding: 25px 20px;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
      letter-spacing: 1px;
    }
    .content {
      padding: 30px 25px;
      color: #333333;
      line-height: 1.6;
    }
    .footer {
      background: #f1f3f5;
      color: #666;
      text-align: center;
      font-size: 13px;
      padding: 15px;
    }
  </style>
</head>
<body>

  <div class="email-wrapper">
    <div class="header">
      <h1>Account Deactivated</h1>
    </div>

    <div class="content">
      <p>
        Dear <strong><?php echo $user_name ?? 'User' ?></strong>,
      </p>
      <p>
        Your SNTC account has been deactivated by the administrator.
        You will not be able to sign in until your account is re-activated.
      </p>
      <p>
        If you believe this was done in error, or you need assistance,
        please contact us at <strong><a href="mailto:info@sntcgroup.com">info@sntcgroup.com</a></strong>.
      </p>
    </div>
    <p style="font-size:0.9em;padding:0 25px 20px;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
    <hr style="border:none;border-top:1px solid #eee" />
  </div>

</body>
</html>
