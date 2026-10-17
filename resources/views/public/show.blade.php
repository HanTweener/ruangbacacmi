@extends('layouts.app')
@section('title', $article->judul)
@section('content')
    <div class="container reading-page">
        <a class="back-link" href="{{ route('home') }}#artikel">
            ← Kembali ke semua artikel
        </a>
        <header class="article-heading">
            <span class="category">
                {{ $article->kategori }}
            </span>
            <h1>{{ $article->judul }}</h1>
            <div class="reading-meta">
                <span class="avatar">
                    {{ mb_substr($article->user->name, 0, 1) }}
                </span>
                <span>
                    {{ $article->user->name }}
                </span>
                <span>
                    ·
                </span>
                <time datetime="{{ $article->created_at->toDateString() }}">
                    {{ $article->created_at->translatedFormat('d F Y') }}
                </time>
            </div>
        </header>
        <img
            class="reading-cover"
            src="{{ $article->gambar ? route('article.image', $article) : asset('assets/editorial-' . $article->id % 3 . '.svg') }}"
            alt="Ilustrasi artikel {{ $article->judul }}"
            width="1000"
            height="540"
        >
        <div class="reading-body">{{ $article->isi }}</div>
        <div class="reading-footnote">
            Konten demonstrasi untuk tes rekrutmen. Artikel ini bukan saran medis.
        </div>
    </div>
    @if ($related->isNotEmpty())
        <section class="container articles-section">
            <div class="section-heading">
                <h2>Artikel lainnya<span>.</span></h2>
                <a href="{{ route('home') }}#artikel">
                    Semua artikel ↗
                </a>
            </div>
            <div class="article-grid">
                @foreach ($related as $article)
                    @include('partials.card')
                @endforeach
            </div>
        </section>
    @endif
@endsection
