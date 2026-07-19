<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset your password</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family: 'Segoe UI', Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.06);">
                    <tr>
                        <td style="background:#4f46e5; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:20px; font-weight:700;">TUP-LMS</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px; color:#111827; font-size:20px;">Reset your password</h2>
                            <p style="margin:0 0 16px; color:#374151; font-size:15px; line-height:1.6;">
                                Hi {{ $userName }},
                            </p>
                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                We received a request to reset the password for your TUP-LMS account. Click the button below to choose a new password. This link will expire in 60 minutes.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius:8px; background:#4f46e5;">
                                        <a href="{{ $resetUrl }}"
                                           style="display:inline-block; padding:12px 28px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:600; border-radius:8px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:24px 0 0; color:#6b7280; font-size:13px; line-height:1.6;">
                                If you didn't request a password reset, you can safely ignore this email — your password will remain unchanged.
                            </p>
                            <p style="margin:16px 0 0; color:#9ca3af; font-size:12px; line-height:1.6;">
                                If the button above doesn't work, copy and paste this link into your browser:<br>
                                <a href="{{ $resetUrl }}" style="color:#4f46e5; word-break:break-all;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background:#f9fafb; border-top:1px solid #eee;">
                            <p style="margin:0; color:#9ca3af; font-size:12px;">&copy; {{ date('Y') }} TUP-LMS &mdash; TUP-Taguig Learning Management System</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
