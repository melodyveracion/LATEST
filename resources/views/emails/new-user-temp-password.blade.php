<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ConsoliData Temporary Password</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="color: #0a7075;">ConsoliData Account Created</h2>

    <p>Hello {{ $name }},</p>

    <p>Your {{ $roleLabel }} account has been created in <strong>ConsoliData</strong>.</p>

    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Temporary Password:</strong> {{ $temporaryPassword }}</p>

    <p>Please log in using the button below, then change your password immediately.</p>

    <p>
        <a href="{{ $loginUrl }}"
           style="display: inline-block; background: #0a7075; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 6px;">
            Go to Login
        </a>
    </p>

    <p>If the button does not work, use this link:</p>
    <p><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>

    <p>Thank you.</p>
</body>
</html>
