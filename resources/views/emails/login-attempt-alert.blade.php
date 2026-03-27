<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConsoliData Login Attempt Alert</title>
</head>
<body style="margin: 0; padding: 24px 12px; background-color: #f3f6f8; font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 640px; background-color: #ffffff; border: 1px solid #d7e0e4; border-radius: 16px; overflow: hidden;">
                    <tr>
                        <td style="padding: 24px 28px; background-color: #7c2d12; color: #ffffff;">
                            <div style="font-size: 22px; font-weight: 700; margin-bottom: 6px;">ConsoliData</div>
                            <div style="font-size: 14px; opacity: 0.92;">Login Attempt Alert</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin: 0 0 16px;">Hello,</p>

                            <p style="margin: 0 0 18px; line-height: 1.7;">
                                We detected multiple failed login attempts for your ConsoliData account.
                            </p>

                            <div style="margin: 0 0 20px; padding: 20px; background-color: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px;">
                                <div style="font-size: 16px; font-weight: 700; margin-bottom: 8px; color: #9a3412;">Secure your account</div>
                                <div style="font-size: 14px; line-height: 1.7; margin-bottom: 16px; color: #7c2d12;">
                                    If this was you, use the button below to verify your email and recover access.
                                </div>
                                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 20px; background-color: #9a3412; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 700;">
                                    Recover Account
                                </a>
                            </div>

                            <p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">
                                If the button does not work, open this link manually:
                            </p>
                            <p style="margin: 0 0 16px; font-size: 13px; word-break: break-all;">
                                <a href="{{ $resetUrl }}" style="color: #9a3412;">{{ $resetUrl }}</a>
                            </p>

                            <p style="margin: 0; font-size: 13px; line-height: 1.7; color: #64748b;">
                                If you did not try to log in, you can ignore this message and contact your administrator.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
