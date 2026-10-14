@extends('layouts.console')
@section('title', 'Pengaturan')
@section('content')
<form class="k-card" id="set-form" data-settings novalidate>
    <div id="set-groups" class="flex flex-col gap-6"><p class="body-md">Memuat…</p></div>
    <p class="c-error mt-4" id="set-error" role="alert" hidden></p>
    <div id="set-msg" class="k-result mt-3" role="status" aria-live="polite"></div>
    <div class="k-row mt-5"><button type="submit" class="k-btn" id="set-save">Simpan pengaturan</button></div>
</form>
@endsection
