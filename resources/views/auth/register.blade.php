@extends('layouts.auth')
@section('title', 'Daftar')
@section('auth-content')
    <span class="eyebrow">AKUN PENULIS</span>
    <h2>Daftar akun.</h2>
    <p class="muted">Gunakan akun untuk membuat dan mengelola artikel.</p>
    <form
        class="stack-form"
        method="post"
        action="{{ route('register') }}"
    >
        @csrf
        <label for="name">
            Nama lengkap
        </label>
        <input
            id="name"
            name="name"
            value="{{ old('name') }}"
            maxlength="100"
            autocomplete="name"
            required
            autofocus
        >
        <label for="email">
            Alamat email
        </label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            maxlength="254"
            autocomplete="email"
            required
        >
        <label for="password">
            Kata sandi
        </label>
        <input
            id="password"
            name="password"
            type="password"
            minlength="12"
            autocomplete="new-password"
            aria-describedby="password-help"
            required
        >
        <small id="password-help">
            Minimal 12 karakter, dengan huruf dan angka.
        </small>
        <label for="password_confirmation">
            Konfirmasi kata sandi
        </label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
            required
        >
        <button class="btn btn-full" type="submit">
            Buat akun penulis ↗
        </button>
    </form>
    <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
@endsection
