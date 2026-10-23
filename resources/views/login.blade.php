<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Masuk · Centaurian Run</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/console/main.js'])
</head>
<body class="font-sans k-body">
<main class="k-login">
    <form id="login-form" class="c-step k-login_card" novalidate>
        <h1 class="heading-xs">Masuk panitia dan admin</h1>
        <div class="c-field mt-5">
            <label class="c-label" for="username">Username</label>
            <input class="c-input" id="username" name="username" type="text" autocomplete="username" autocapitalize="off" spellcheck="false" aria-describedby="login-error">
        </div>
        <div class="c-field mt-4">
            <label class="c-label" for="password">Password</label>
            <input class="c-input" id="password" name="password" type="password" autocomplete="current-password" aria-describedby="login-error">
        </div>
        <p class="c-error mt-4" id="login-error" role="alert" hidden></p>
        <div class="mt-6">
            @include('partials.btn', ['type' => 'submit', 'label' => 'Masuk', 'color' => 'green', 'primary' => true, 'attributes' => 'id="login-submit"'])
        </div>
        <p class="body-md mt-5"><a class="c-link" href="/">← Kembali ke situs</a></p>
    </form>
</main>
</body>
</html>
