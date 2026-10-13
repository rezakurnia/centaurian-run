<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil {{ $no }} · Centaurian Run</title>
    <meta name="description" content="Waktu tempuh peserta {{ $no }} di Centaurian Run.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

@include('partials.nav', ['base' => '/'])

<main class="bg-beige">
    <header class="rounded-b-lg bg-(image:--gradient-hero) pt-36 pb-fluid-2xl max-md:pt-32">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14">
                    <p class="body-md uppercase">Hasil lomba</p>
                    <h1 class="heading-lg mt-4 max-w-[14ch] text-balance">{{ $no }}</h1>
                </div>
            </div>
        </div>
    </header>

    <section class="py-fluid-2xl">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14">
                    <div id="result-detail" data-no="{{ $no }}">
                        <p class="c-step body-lg" id="result-status" role="status">Memuat hasil…</p>
                        <div class="c-step" id="result-card" hidden>
                            <h2 class="heading-xs" data-f="name"></h2>
                            <p class="body-md mt-1"><span data-f="number"></span> · <span data-f="category"></span></p>
                            <p class="heading-md mt-6 tabular-nums" data-f="duration"></p>
                            <p class="body-md mt-2">Start <span data-f="start"></span> · Finish <span data-f="finish"></span></p>
                        </div>
                    </div>
                    <div class="mt-gutter"><a class="c-link" href="/hasil">← Semua hasil</a></div>
                </div>
            </div>
        </div>
    </section>

    <div class="rounded-t-lg bg-green py-fluid-2xl">
        <div class="container">@include('partials.footer')</div>
    </div>
</main>

</body>
</html>
