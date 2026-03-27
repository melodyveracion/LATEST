<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - ConsoliData</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>

<div class="overlay"></div>

<div class="container">

    <div class="left">
        <h1>ConsoliData</h1>
        <p>Create Property Management Office Administrative Account</p>
    </div>

    <div class="login-box">
        <h2>Create Admin Account</h2>
        <p>Set up the initial administrative account for the procurement system.</p>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="/admin/register">
            @csrf

            <label>Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit">Create Account</button>
        </form>

        <div class="signup-link">
            Already have an account?
            <a href="/admin/login">Login</a>
        </div>
    </div>

</div>

</body>
</html>