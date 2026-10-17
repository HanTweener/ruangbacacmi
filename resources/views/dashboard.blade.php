@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <section class="container workspace">
        <div class="page-heading">
            <div>
                <span class="eyebrow">
                    RUANG PENULIS
                </span>
                <h1>Halo, {{ auth()->user()->name }}<span>.</span></h1>
                <p>Kelola Artikel Anda</p>
            </div>
            <a class="btn" href="{{ route('articles.create') }}">
                ＋ Tambah artikel
            </a>
        </div>
        <div class="stats">
            <div class="stat">
                <span>
                    Semua artikel
                </span>
                <strong>
                    {{ $total }}
                </strong>
                <small>
                    Artikel milik Anda
                </small>
            </div>
            <div class="stat">
                <span>
                    <i class="status-dot published"></i> Published
                </span>
                <strong>
                    {{ $published }}
                </strong>
                <small>
                    Sudah dapat dibaca publik
                </small>
            </div>
            <div class="stat">
                <span>
                    <i class="status-dot draft"></i> Draft
                </span>
                <strong>
                    {{ $draft }}
                </strong>
                <small>
                    Belum dipublikasikan
                </small>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <h2>Yang terbaru dari Anda</h2>
                <a href="{{ route('articles.index') }}">
                    Lihat semua ↗
                </a>
            </div>
            @include('partials.article-table')
        </div>
    </section>
@endsection
