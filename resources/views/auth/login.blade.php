@extends('layouts.auth')
@section('title', 'Masuk')
@section('auth-content')
    <span class="eyebrow">UNTUK PENULIS</span>
    <h2>Senang berjumpa lagi.</h2>
    <p class="muted">Masuk untuk mengelola artikel Anda.</p>
    <form
        class="stack-form"
        method="post"
        action="{{ route('login') }}"
    >
        @csrf
        <label for="email">
            Alamat email
        </label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            autocomplete="email"
            placeholder="nama@email.com"
            required
            autofocus
        >
        <div class="label-row">
            <label for="password">
                Kata sandi
            </label>
            <a href="{{ route('password.request') }}">
                Lupa kata sandi?
            </a>
        </div>
        <div class="password-field">
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
            >
            <button
                type="button"
                data-password="password"
                aria-label="Tampilkan kata sandi"
                aria-pressed="false"
            >
                Lihat
            </button>
        </div>
        <button class="btn btn-full" type="submit">
            Masuk <span aria-hidden="true">↗</span>
        </button>
    </form>
    <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
    <a class="back-link" href="{{ route('home') }}">← Kembali membaca</a>
@endsection
