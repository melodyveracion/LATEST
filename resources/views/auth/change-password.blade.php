<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Change Password - ConsoliData</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>

<div class="overlay"></div>

<div class="container">

    <div class="left">
        <h1>ConsoliData</h1>
        <p>Update your password securely by confirming your current password first.</p>
    </div>

    <div class="login-box">
        <h2>Change Password</h2>
        <p>Use a strong password and confirm your current password before saving the change.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="/change-password">
            @csrf

            <label>Current Password</label>
            <input type="password" name="current_password" required>

            <label>New Password</label>
            <input type="password" name="password" required>

            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit">Update Password</button>
        </form>
    </div>
</div>

</body>
</html>