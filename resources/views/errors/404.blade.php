@extends('layouts.app')
@section('title', 'Halaman Tidak Ditemukan')
@section('content')
    <section class="container empty-state error-page">
        <span class="eyebrow">
            404 · TIDAK DITEMUKAN
        </span>
        <h1>Sepertinya Anda tersesat.</h1>
        <p>Halaman ini tidak tersedia. Masih ada cerita lain untuk Anda.</p>
        <a class="btn" href="{{ route('home') }}">
            Kembali ke ruang baca ↗
        </a>
    </section>
@endsection
