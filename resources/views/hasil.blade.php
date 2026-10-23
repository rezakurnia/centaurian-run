<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil lomba · Centaurian Run</title>
    <meta name="description" content="Waktu tempuh peserta Centaurian Run, diurutkan dari yang tercepat.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

@include('partials.nav', ['base' => '/'])

<main id="main" class="bg-beige">
    <header class="rounded-b-lg bg-(image:--gradient-hero) pt-32 pb-fluid-lg max-md:pt-28">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14 text-center">
                    <p class="body-md uppercase">Hasil lomba</p>
                    <h1 class="heading-lg mx-auto mt-4 max-w-[22ch] text-balance">Siapa yang tercepat?</h1>
                    <p class="body-lg mx-auto mt-4 max-w-140 text-balance">Waktu tempuh dicatat dari scan start sampai scan finish, diurutkan dari yang tercepat.</p>
                </div>
            </div>
        </div>
    </header>

    <section class="py-fluid-2xl">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14">
                    <div class="c-step" id="results" data-url="/api/results">
                        <div class="c-field sm:max-w-80">
                            <label class="c-label" for="category-filter">Kategori</label>
                            <select class="c-input" id="category-filter">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <p class="body-lg mt-6" id="results-status" role="status">Memuat hasil…</p>
                        <p class="c-error mt-4" id="results-error" role="alert" hidden></p>

                        <div class="mt-6 overflow-x-auto" id="results-wrap" tabindex="0" role="region" aria-label="Tabel hasil lomba" hidden>
                            <table class="c-table">
                                <caption class="sr-only">Hasil lomba, diurutkan dari waktu tercepat</caption>
                                <thead>
                                    <tr><th scope="col">#</th><th scope="col">Nama</th><th scope="col">Kategori</th><th scope="col">No. peserta</th><th scope="col" class="text-right">Waktu</th></tr>
                                </thead>
                                <tbody id="results-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="c-step mt-gutter">
                        <h2 class="heading-xs">Belum tahu statusmu?</h2>
                        <p class="body-md mt-3">Cek pendaftaranmu dengan nomor peserta atau email.</p>
                        <div class="mt-4"><a class="c-link" href="/cek-status">BIB Checking →</a></div>
                    </div>
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
