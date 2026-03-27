<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reset Password - ConsoliData</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>

<div class="overlay"></div>

<div class="container">

    <div class="left">
        <h1>ConsoliData</h1>
        <p>Choose a new password now, or close this page and use the temporary password from your email to sign in later.</p>
    </div>

    <div class="login-box">
        <h2>Reset Password Now</h2>
        <p>This option lets you set your new password immediately without logging in first.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ $formAction }}">
            @csrf

            <label>Email Address</label>
            <input type="email" value="{{ $email }}" readonly>

            <label>New Password</label>
            <input type="password" name="password" required>

            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit">Reset Password</button>
        </form>

        <div class="signup-link">
            Prefer the other option?
            <a href="{{ $loginUrl }}">Use the temporary password from your email and change it after login</a>
        </div>
    </div>
</div>

</body>
</html>
