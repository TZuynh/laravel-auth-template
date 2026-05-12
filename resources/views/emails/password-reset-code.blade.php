<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password reset code</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td style="padding:32px 16px;">
            <table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;">
                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#4f46e5;">Secure account access</p>
                        <h1 style="margin:0 0 16px;font-size:24px;line-height:32px;color:#0f172a;">Your password reset code</h1>
                        <p style="margin:0 0 24px;font-size:15px;line-height:24px;color:#475569;">
                            Use this code to reset your password. It expires in {{ $expiresInMinutes }} minutes.
                        </p>
                        <div style="margin:0 0 24px;padding:18px 24px;border-radius:16px;background:#eef2ff;text-align:center;font-size:32px;font-weight:800;letter-spacing:10px;color:#4338ca;">
                            {{ $code }}
                        </div>
                        <p style="margin:0;font-size:13px;line-height:22px;color:#64748b;">
                            If you did not request this code, you can ignore this email. Your password will not change.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
