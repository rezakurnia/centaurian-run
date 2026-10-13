<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · Centaurian Run</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/console/main.js'])
</head>
<body class="font-sans k-body" data-role="{{ $role ?? 'panitia' }}">
<div class="k-shell">
    <aside class="k-side">
        <a class="k-brand" href="/">Centaurian Run</a>
        <nav id="k-nav" class="k-nav" aria-label="Menu console"></nav>
        <div class="k-user">
            <span id="k-user"></span>
            <button type="button" id="k-logout" class="k-btn -ghost">Keluar</button>
        </div>
    </aside>
    <main class="k-main" id="k-main" hidden>
        <h1 class="k-title">@yield('title')</h1>
        @yield('content')
    </main>
</div>
</body>
</html>
