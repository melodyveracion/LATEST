<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConsoliData Password Recovery</title>
</head>
<body style="margin: 0; padding: 24px 12px; background-color: #f3f6f8; font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 640px; background-color: #ffffff; border: 1px solid #d7e0e4; border-radius: 16px; overflow: hidden;">
                    <tr>
                        <td style="padding: 24px 28px; background-color: #0f766e; color: #ffffff;">
                            <div style="font-size: 22px; font-weight: 700; margin-bottom: 6px;">ConsoliData</div>
                            <div style="font-size: 14px; opacity: 0.92;">Password Recovery Options</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin: 0 0 16px;">Hello {{ $name }},</p>

                            <p style="margin: 0 0 20px; line-height: 1.7;">
                                You asked to recover access to your ConsoliData account. Choose the option below that works best for you.
                            </p>

                            <div style="margin: 0 0 20px; padding: 20px; background-color: #f8fafc; border: 1px solid #d7e0e4; border-radius: 12px;">
                                <div style="font-size: 16px; font-weight: 700; margin-bottom: 8px; color: #0f172a;">Option 1: Reset your password now</div>
                                <div style="font-size: 14px; line-height: 1.7; margin-bottom: 16px; color: #475569;">
                                    Open the secure reset page and set your new password immediately.
                                </div>
                                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 20px; background-color: #0f766e; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 700;">
                                    Reset Password Now
                                </a>
                            </div>

                            <div style="margin: 0 0 20px; padding: 20px; background-color: #f8fafc; border: 1px solid #d7e0e4; border-radius: 12px;">
                                <div style="font-size: 16px; font-weight: 700; margin-bottom: 8px; color: #0f172a;">Option 2: Use your temporary password</div>
                                <div style="font-size: 14px; line-height: 1.7; margin-bottom: 12px; color: #475569;">
                                    Sign in using the temporary password below. The system will ask you to change it after login.
                                </div>
                                <div style="margin: 0 0 16px; padding: 14px 16px; background-color: #ecfeff; border: 1px dashed #14b8a6; border-radius: 10px; font-size: 18px; font-weight: 700; letter-spacing: 1px; color: #134e4a;">
                                    {{ $temporaryPassword }}
                                </div>
                                <a href="{{ $loginUrl }}" style="display: inline-block; padding: 12px 20px; background-color: #1e293b; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 700;">
                                    Go to Login
                                </a>
                            </div>

                            <p style="margin: 0 0 10px; font-size: 13px; color: #64748b;">
                                If you are opening this on a phone, make sure the phone is connected to the same network as the computer hosting the system.
                            </p>
                            <p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">
                                If the buttons do not work, copy and paste these links into your browser:
                            </p>
                            <p style="margin: 0 0 8px; font-size: 13px; word-break: break-all;">
                                <strong>Reset link:</strong> <a href="{{ $resetUrl }}" style="color: #0f766e;">{{ $resetUrl }}</a>
                            </p>
                            <p style="margin: 0; font-size: 13px; word-break: break-all;">
                                <strong>Login link:</strong> <a href="{{ $loginUrl }}" style="color: #0f766e;">{{ $loginUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 18px 28px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; line-height: 1.7; color: #64748b;">
                            This reset link is time-limited for security.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
