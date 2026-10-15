@extends('layouts.console')
@section('title', 'Pengaturan')
@section('content')
{{-- One k-card per group, rendered by settings.js from GET /admin/settings --}}
<form id="settings-form" class="flex flex-col gap-5" novalidate>
    <div id="settings-groups" class="flex flex-col gap-5"><section class="k-card">Memuat…</section></div>
    <div class="k-card">
        <div class="k-row"><button type="submit" class="k-btn" id="settings-save">Simpan pengaturan</button></div>
        <div id="settings-msg" class="k-result mt-3" role="status" aria-live="polite"></div>
    </div>
</form>
@endsection
