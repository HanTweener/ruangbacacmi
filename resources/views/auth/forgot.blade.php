@extends('layouts.auth')
@section('title', 'Lupa Kata Sandi')
@section('auth-content')
    <span class="eyebrow">RESET KATA SANDI</span>
    <h2>Lupa kata sandi?</h2>
    <p class="muted">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>
    <form
        class="stack-form"
        method="post"
        action="{{ route('password.email') }}"
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
            required
            autofocus
        >
        <button class="btn btn-full" type="submit">
            Kirim tautan reset ↗
        </button>
    </form>
    <a class="back-link" href="{{ route('login') }}">← Kembali masuk</a>
@endsection
