<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ConsoliData</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>

<div class="overlay"></div>

<div class="container">
    <div class="left">
        <h1>ConsoliData</h1>
        <p>Secure access to the procurement management system.</p>
    </div>

    <div class="login-box">
        <h2>Login</h2>
        <p>Use your assigned account credentials to continue.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/login" novalidate>
            @csrf

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}">

            <label>Password</label>
            <input type="password" name="password">

            <button type="submit">Login</button>
        </form>
    </div>
</div>
</body>
</html>