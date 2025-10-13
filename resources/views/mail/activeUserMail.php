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
      <h2>Your SNTC Account is Now Active</h2>
      <p>
        Dear <strong>{{ $user_name ?? 'User' }}</strong>,
      </p>
      <p>
        Great news! Your SNTC account has been successfully activated.  
        You can now <strong>post your Buy and Sell queries</strong> and connect with other members of our trading community.
      </p>
      <p>
        Start exploring the marketplace and grow your business with ease.
      </p>

      <!-- <a href="{{ $login_url ?? '#' }}" class="cta-button">Go to My Account</a> -->

      <p style="margin-top: 25px;">
        If you have any questions or need help, feel free to contact our support team at  
        <a href="mailto:support@sntc.com">support@sntc.com</a>.
      </p>
      <p>Welcome to the SNTC family!</p>
    </div>
    <div class="footer">
      © {{ date('Y') }} SNTC. All rights reserved.  
      <br>Empowering smart trade connections.
    </div>
  </div>

</body>
</html>
