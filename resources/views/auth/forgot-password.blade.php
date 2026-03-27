<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ConsoliData</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>

<div class="overlay"></div>

<div class="container">
    <div class="left">
        <h1>ConsoliData</h1>
        <p>Enter your email address to receive two recovery options: reset your password now or use a temporary password and change it after login.</p>
    </div>

    <div class="login-box">
        <h2>Forgot Password</h2>
        <p>Enter the email tied to your account and the system will send both a reset link and a temporary password.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <label>Email Address</label>
            <input type="email" name="email" value="{{ old('email', request('email')) }}" required>

            <button type="submit">Send Recovery Options</button>
        </form>

        <div class="signup-link">
            <a href="{{ url('/') }}">Back to Role Selection</a>
        </div>
    </div>
</div>

</body>
</html>
