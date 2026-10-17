@extends('layouts.app')
@section('title', 'Jelajahi Artikel')
@section('content')
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <span class="eyebrow">
                    <span class="tiny-cross">✚</span> RUANG BACA KLINIK UTAMA CMI
                </span>
                <h1>Baca artikel.<br><span>Mulai menulis.</span></h1>
                <p>Artikel tentang kesehatan, gaya hidup, nutrisi, dan kegiatan. Pilih kategori atau cari topik yang ingin
                    Anda baca.</p>
                <a class="btn" href="#artikel">
                    Lihat artikel <span aria-hidden="true">↓</span>
                </a>
            </div>
            <div class="hero-visual">
                <img
                    src="{{ asset('assets/hero.svg') }}"
                    alt="Ilustrasi editorial tanaman, buku terbuka, dan cahaya matahari"
                    width="640"
                    height="560"
                >
            </div>
        </div>
    </section>
    <section
        class="container articles-section"
        id="artikel"
        data-article-navigation="{{ route('home') }}"
    >
        <p
            class="sr-only"
            role="status"
            aria-live="polite"
            data-article-status
        ></p>
        <div
            class="alert error"
            role="alert"
            data-article-error
            hidden
        >
            Artikel gagal dimuat. Hasil sebelumnya tetap ditampilkan.
            <a href="{{ url()->full() }}#artikel" data-article-retry>Coba lagi</a>
        </div>
        <div data-article-content>
            <div class="section-heading">
                <div>
                    <span class="eyebrow">
                        ARTIKEL
                    </span>
                    <h2 tabindex="-1">
                        {{ request('kategori') ? 'Artikel ' . request('kategori') : 'Artikel terbaru' }}<span>.</span></h2>
                </div>
            </div>
            <div class="browse-controls">
                <nav class="category-tabs" aria-label="Kategori artikel">
                    <a
                        href="{{ route('home', array_filter(['q' => request('q')], fn($value) => filled($value))) }}#artikel"
                        class="{{ !request('kategori') ? 'selected' : '' }}"
                        aria-current="{{ !request('kategori') ? 'page' : 'false' }}"
                    >
                        Semua artikel
                    </a>
                    @foreach (\App\Models\Article::CATEGORIES as $category)
                        <a
                            class="{{ request('kategori') === $category ? 'selected' : '' }}"
                            aria-current="{{ request('kategori') === $category ? 'page' : 'false' }}"
                            href="{{ route('home', array_filter(['kategori' => $category, 'q' => request('q')], fn($value) => filled($value))) }}#artikel"
                        >
                            {{ $category }}
                        </a>
                    @endforeach
                </nav>
                <form
                    class="search-form"
                    method="get"
                    action="{{ route('home') }}#artikel"
                >
                    <label class="sr-only" for="public-search">
                        Cari artikel
                    </label>
                    <input
                        id="public-search"
                        name="q"
                        type="search"
                        maxlength="100"
                        placeholder="Cari bacaan..."
                        value="{{ request('q') }}"
                    >
                    @if (request('kategori'))
                        <input
                            type="hidden"
                            name="kategori"
                            value="{{ request('kategori') }}"
                        >
                    @endif
                    <button type="submit" aria-label="Cari artikel">
                        ⌕
                    </button>
                </form>
            </div>
            @if (request()->filled('q'))
                <p class="result-note">{{ $articles->total() }} hasil untuk “{{ request('q') }}” · <a
                        href="{{ route('home') }}#artikel" data-article-reset
                    >Reset pencarian</a></p>
            @endif
            <div class="article-grid">
                @forelse($articles as $article)
                    @include('partials.card')
                @empty
                    <div class="empty-state">
                        <span aria-hidden="true">
                            ⌕
                        </span>
                        <h3>Belum ada bacaan di sini.</h3>
                        <p>Coba kata kunci lain atau jelajahi semua artikel.</p>
                        <a
                            class="btn btn-outline"
                            href="{{ route('home') }}#artikel"
                            data-article-reset
                        >
                            Lihat semua artikel
                        </a>
                    </div>
                @endforelse
            </div>
            {{ $articles->links() }}
        </div>
    </section>
    <section class="container">
        <div class="write-banner">
            <div>
                <span class="eyebrow">
                    AKUN PENULIS
                </span>
                <h2>Tulis artikel Anda.</h2>
            </div>
            <a class="btn btn-white" href="{{ auth()->check() ? route('articles.create') : route('register') }}">
                {{ auth()->check() ? 'Tambah artikel' : 'Daftar untuk menulis' }} ↗
            </a>
        </div>
    </section>
@endsection
