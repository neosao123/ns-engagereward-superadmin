<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patsanstha Onboarding Success | {{ config('app.name') }}</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f6f8;
      margin: 0;
      padding: 0;
      color: #333333;
    }

    .container {
      max-width: 600px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      padding: 30px;
    }

    .header {
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      color: #3d22b5;
      margin-bottom: 20px;
    }

    .content p {
      font-size: 14px;
      line-height: 1.6;
      margin: 16px 0;
    }

    .detail {
      margin: 12px 0;
    }

    .detail strong {
      display: inline-block;
      width: 120px;

    }

    .btn-login {
      display: inline-block;
      background-color: #3a2dd3;
      color: #ffffff;
      padding: 12px 20px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      margin-top: 20px;
    }

    .footer {
      text-align: center;
      font-size: 12px;

      margin-top: 30px;
    }

    @media screen and (max-width: 600px) {
      .container {
        margin: 20px;
        padding: 20px;
      }
      .header {
        font-size: 20px;
      }
      .btn-login {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      Company Registration Details
    </div>
    <div class="content">
      <p>Hello,</p>
      <p>Congratulations! Your Company has been successfully onboarded. Below are your account details:</p>

      <div class="detail"><strong>Company Name:</strong> {{ $details['name'] }}</div>
      <div class="detail"><strong>URL:</strong> <a href="{{ $details['url'] }}" target="_blank">{{ $details['url'] }}</a></div>
      <div class="detail"><strong>User ID:</strong> {{ $details['user_id'] }}</div>
      <div class="detail"><strong>Password:</strong> {{ $details['password'] }}</div>

      <a href="{{ $details['url'] }}" class="btn-login" target="_blank" style="color:white">LOGIN TO DASHBOARD</a>

      <p>Please make sure to change your password after your first login for security purposes.</p>

      <p style="color: #000000 !important;">
        Didn’t request this? Just ignore this email or contact us at
        <a href="mailto:{{ env('SUPPORT_MAIL') }}">
            {{ env('SUPPORT_MAIL') }}
        </a>.
      </p>
      <p>Thank you,<br>{{ config('app.name') }} Team</p>
    </div>
  </div>

  <div class="footer">
    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
  </div>
</body>

</html>
