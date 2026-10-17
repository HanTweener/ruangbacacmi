@extends('layouts.app')
@section('title', 'Terlalu Banyak Percobaan')
@section('content')
    <section class="container empty-state error-page">
        <span class="eyebrow">
            429 · JEDA SEJENAK
        </span>
        <h1>Coba lagi sebentar lagi.</h1>
        <p>Terlalu banyak percobaan. Tunggu satu menit sebelum mencoba kembali.</p>
        <a class="btn" href="{{ route('home') }}">
            Kembali membaca ↗
        </a>
    </section>
@endsection
