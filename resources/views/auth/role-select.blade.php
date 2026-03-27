<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConsoliData - University Procurement</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    @include('auth.partials.no-cache-guard')
</head>

<body>

    <div class="overlay"></div>

    <div class="content">
        <h1>ConsoliData</h1>
        <p>A Purchase Management System for the Property Management and Supply Office</p>

        <div class="roles">
            <a href="/unit/login" class="role-btn role-btn-unit">Unit / Colleges</a>
            <a href="/bac/login" class="role-btn role-btn-bac">Bidding and Award Committe</a>
            <a href="/admin/login" class="role-btn role-btn-admin">Property Management Office</a>
        </div>
    </div>

</body>

</html>