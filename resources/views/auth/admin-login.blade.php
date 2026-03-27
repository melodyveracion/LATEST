<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ConsoliData</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>

<div class="overlay"></div>

<div class="container">
    <div class="left">
        <h1>ConsoliData</h1>
        <p>University procurement administration. Validate PPMP and purchase requests, manage users, and monitor reports.</p>
    </div>

    <div class="login-box">
        <h2>Admin Login</h2>
        <p>Access validation, reporting, and user management tools from one secure workspace.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (session('info'))
            <div class="info-note">
                {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="/admin/login" novalidate>
            @csrf

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}">

            <label>Password</label>
            <input type="password" name="password">

            <div class="forgot-password-wrap">
                <a href="{{ route('password.request') }}">Forgot Password?</a>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="signup-link">
            Don’t have an account?
            <a href="/admin/register">Create Account</a>
        </div>

        <div class="signup-link">
            <a href="{{ route('landing') }}" class="link-underlined">Back to landing page</a>
        </div>
    </div>
</div>

</body>
</html>
