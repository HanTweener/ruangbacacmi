@extends('layouts.auth')
@section('title', 'Masuk')
@section('auth-content')
    <span class="eyebrow">UNTUK PENULIS</span>
    <h2>Senang berjumpa lagi.</h2>
    <p class="muted">Masuk untuk mengelola artikel Anda.</p>
    <div style="background: var(--sand, #f8f6f0); border: 1px solid var(--line, #e3e3e3); border-radius: 6px; padding: 12px 14px; margin: 15px 0 20px; font-size: 13px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <strong style="color: var(--navy, #002244); font-size: 12px; letter-spacing: 0.5px;">AKUN DEMO PENGUJIAN</strong>
            <span style="font-size: 10px; background: #eef8f2; color: #236747; border: 1px solid #b7d9c6; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Aktif</span>
        </div>
        <div style="display: grid; gap: 4px; font-size: 12px; color: var(--charcoal, #1f1f1f); line-height: 1.5;">
            <div><span>Email:</span> <code style="font-size: 11px; background: #fff; padding: 2px 6px; border-radius: 3px; border: 1px solid #e3e3e3;">redaksi@example.test</code></div>
            <div><span>Sandi:</span> <code style="font-size: 11px; background: #fff; padding: 2px 6px; border-radius: 3px; border: 1px solid #e3e3e3;">Password123456</code></div>
        </div>
    </div>
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
