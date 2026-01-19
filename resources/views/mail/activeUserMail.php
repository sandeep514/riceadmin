<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Activated - SNTC</title>
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
      background-color: #004aad;
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
    .content h2 {
      color: #004aad;
      margin-top: 0;
    }
    .cta-button {
      display: inline-block;
      margin-top: 20px;
      background-color: #004aad;
      color: #ffffff !important;
      padding: 12px 24px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 600;
    }
    .cta-button:hover {
      background-color: #00388a;
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
      <h1>🎉 Congratulations!</h1>
    </div>

    <div class="content">
      <p>
        Dear <strong><?php echo $user_name ?? 'User' ?></strong>,
      </p>
      <p>
        Great news! Your SNTC account is now activated. You can start exploring the platform and grow your business with SNTC..
      </p>
      <p>
        If you need any assistance, feel free to reach us at <strong><a href="mailto:info@sntcgroup.com">info@sntcgroup.com</a></strong>
      </p>


      <p style="margin-top: 25px;">
        Welcome to the SNTC family!
      </p>
    </div>
    <p style="font-size:0.9em;">Regards,<br />SNTC Agro Technology Pvt. Ltd.</p>
    <hr style="border:none;border-top:1px solid #eee" />
  </div>

</body>
</html>
