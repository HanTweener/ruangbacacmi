@extends('layouts.app')
@section('content')
    <section class="container auth-grid">
        <aside class="auth-story">
            <span class="eyebrow">
                AKUN PENULIS
            </span>
            <h1>Kelola Artikel Anda</h1>
            <img
                src="{{ asset('assets/editorial-1.svg') }}"
                alt="Ilustrasi buku dan tanaman"
                width="500"
                height="320"
            >
        </aside>
        <div class="auth-panel">
            @yield('auth-content')
        </div>
    </section>
@endsection
