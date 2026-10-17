@extends('layouts.app')
@section('title', 'Artikel Saya')
@section('content')
    <section class="container workspace">
        <div class="page-heading">
            <div>
                <span class="eyebrow">
                    ARTIKEL SAYA
                </span>
                <h1>Artikel saya<span>.</span></h1>
                <p>Tambah, edit, dan atur status publikasi artikel Anda.</p>
            </div>
            <a class="btn" href="{{ route('articles.create') }}">
                ＋ Tambah artikel
            </a>
        </div>
        <form class="filter-bar" method="get">
            <div>
                <label for="q">
                    Cari judul
                </label>
                <input
                    id="q"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    maxlength="100"
                    placeholder="Cari artikel Anda..."
                >
            </div>
            <div>
                <label for="status">
                    Status
                </label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    <option value="1" @selected(request('status') === '1')>Published</option>
                    <option value="0" @selected(request('status') === '0')>Draft</option>
                </select>
            </div>
            <button class="btn" type="submit">
                Terapkan
            </button>
            <a href="{{ route('articles.index') }}">
                Reset
            </a>
        </form>
        <div class="panel">
            @include('partials.article-table')
        </div>
        {{ $articles->links() }}
    </section>
@endsection
