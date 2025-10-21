<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
</head>
<body style="background: linear-gradient(to bottom right, #eef2ff, #e0e7ff); font-family: 'Inter', Arial, sans-serif; padding: 40px; color: #1e293b;">
<table align="center" width="100%" style="max-width: 600px; background: white; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); overflow: hidden;">
    <tr>
        <td style="padding: 40px 30px;">
            <h1 style="font-size: 24px; color: #4f46e5; margin-bottom: 16px; text-align:center;">
                🔒 Password Reset Request
            </h1>

            <p style="font-size: 15px; color: #374151; line-height: 1.6;">
                Hello, <br><br>
                You are receiving this email because we received a password reset request for your account.
            </p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $url }}"
                   style="background: #4f46e5; color: white; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
                    Reset Password
                </a>
            </div>

            <p style="font-size: 14px; color: #6b7280; line-height: 1.6;">
                This password reset link will expire in 60 minutes. <br>
                If you did not request a password reset, no further action is required.
            </p>

            <hr style="border:none; border-top:1px solid #e5e7eb; margin:30px 0;">

            <p style="font-size: 13px; color: #9ca3af; text-align:center;">
                &copy; {{ date('Y') }} Laravel Auth Template. All rights reserved.
            </p>
        </td>
    </tr>
</table>
</body>
</html>
