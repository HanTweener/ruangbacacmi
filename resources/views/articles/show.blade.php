@extends('layouts.app')
@section('title', 'Pratinjau: ' . $article->judul)
@section('content')
    <div class="container reading-page">
        <a class="back-link" href="{{ route('articles.index') }}">
            ← Kembali ke artikel saya
        </a>
        <div class="preview-toolbar">
            <span class="badge {{ $article->status ? 'published' : 'draft' }}">
                {{ $article->status ? 'Published' : 'Draft — hanya Anda yang dapat melihat' }}
            </span>
            <a class="btn btn-small" href="{{ route('articles.edit', $article) }}">
                Edit artikel ↗
            </a>
        </div>
        <header class="article-heading">
            <span class="category">
                {{ $article->kategori }}
            </span>
            <h1>{{ $article->judul }}</h1>
            <p>{{ $article->user->name }} · {{ $article->created_at->translatedFormat('d F Y') }}</p>
        </header>
        @if ($article->gambar)
            <img
                class="reading-cover"
                src="{{ route('article.image', $article) }}"
                alt="Sampul artikel"
                width="1000"
                height="540"
            >
        @endif
        <div class="reading-body">{{ $article->isi }}</div>
    </div>
@endsection
