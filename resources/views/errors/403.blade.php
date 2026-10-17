@extends('layouts.app')
@section('title', 'Akses Terbatas')
@section('content')
    <section class="container empty-state error-page">
        <span class="eyebrow">
            403 · AKSES TERBATAS
        </span>
        <h1>Ini ruang penulis lain.</h1>
        <p>Anda hanya dapat mengelola artikel milik Anda sendiri.</p>
        <a class="btn" href="{{ route('articles.index') }}">
            Buka artikel saya ↗
        </a>
    </section>
@endsection
