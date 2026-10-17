@extends('layouts.auth')
@section('title', 'Reset Kata Sandi')
@section('auth-content')
    <span class="eyebrow">KEAMANAN AKUN</span>
    <h2>Buat kata sandi baru.</h2>
    <p class="muted">Gunakan kata sandi yang kuat untuk akun Anda.</p>
    <form
        class="stack-form"
        method="post"
        action="{{ route('password.update') }}"
    >
        @csrf
        <input
            type="hidden"
            name="token"
            value="{{ $token }}"
        >
        <label for="email">
            Alamat email
        </label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', request('email')) }}"
            autocomplete="email"
            required
        >
        <label for="password">
            Kata sandi baru
        </label>
        <input
            id="password"
            name="password"
            type="password"
            minlength="12"
            autocomplete="new-password"
            required
            aria-describedby="password-help"
        >
        <small id="password-help">
            Minimal 12 karakter, dengan huruf dan angka.
        </small>
        <label for="password_confirmation">
            Konfirmasi kata sandi baru
        </label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
            required
        >
        <button class="btn btn-full" type="submit">
            Simpan kata sandi baru ↗
        </button>
    </form>
    <a class="back-link" href="{{ route('password.request') }}">Minta tautan baru</a>
@endsection
