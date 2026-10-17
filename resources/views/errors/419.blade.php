@extends('layouts.app')
@section('title', 'Sesi Berakhir')
@section('content')
    <section class="container empty-state error-page">
        <span class="eyebrow">
            419 · SESI BERAKHIR
        </span>
        <h1>Mari mulai kembali.</h1>
        <p>Sesi formulir Anda telah berakhir. Muat ulang halaman sebelum mencoba kembali.</p>
        <a class="btn" href="{{ route('login') }}">
            Masuk kembali ↗
        </a>
    </section>
@endsection
