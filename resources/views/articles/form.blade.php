@extends('layouts.app')
@section('title', $article->exists ? 'Edit Artikel' : 'Tambah Artikel')
@section('content')
    <section class="container workspace">
        <a class="back-link" href="{{ route('articles.index') }}">
            ← Kembali ke artikel saya
        </a>
        <div class="page-heading">
            <div>
                <span class="eyebrow">
                    ARTIKEL SAYA
                </span>
                <h1>{{ $article->exists ? 'Edit artikel' : 'Tambah artikel' }}<span>.</span></h1>
                <p>Isi judul dan konten, lalu pilih status publikasi.</p>
            </div>
        </div>
        <form
            class="editor-grid"
            method="post"
            enctype="multipart/form-data"
            action="{{ $article->exists ? route('articles.update', $article) : route('articles.store') }}"
        >
            @csrf
            @if ($article->exists)
                @method('PUT')
            @endif
            <div class="panel editor-main stack-form">
                <label for="judul">
                    Judul artikel
                    <span class="muted">(wajib)</span>
                </label>
                <input
                    class="title-input"
                    id="judul"
                    name="judul"
                    value="{{ old('judul', $article->judul) }}"
                    minlength="5"
                    maxlength="180"
                    placeholder="Judul artikel"
                    required
                    autofocus
                >
                <label for="isi">
                    Isi artikel
                    <span class="muted">(wajib)</span>
                </label>
                <textarea
                    id="isi"
                    name="isi"
                    rows="18"
                    minlength="30"
                    maxlength="50000"
                    placeholder="Tulis isi artikel di sini."
                    required
                    aria-describedby="content-help"
                >{{ old('isi', $article->isi) }}</textarea>
                <small id="content-help">
                    Minimal 30 karakter. Teks biasa; pisahkan paragraf dengan baris kosong.
                </small>
            </div>
            <aside class="editor-sidebar">
                <div class="panel stack-form">
                    <h2>Pengaturan artikel</h2>
                    <label for="kategori">
                        Kategori
                    </label>
                    <select
                        id="kategori"
                        name="kategori"
                        required
                    >
                        @foreach (\App\Models\Article::CATEGORIES as $category)
                            <option @selected(old('kategori', $article->kategori ?? 'Kesehatan') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    <label for="status">
                        Status publikasi
                    </label>
                    <select
                        id="status"
                        name="status"
                        required
                    >
                        <option value="0" @selected((string) old('status', (int) $article->status) === '0')>Draft — hanya Anda</option>
                        <option value="1" @selected((string) old('status', (int) $article->status) === '1')>Published — dapat dibaca publik</option>
                    </select>
                    <p class="muted">Draft hanya dapat dilihat oleh Anda.</p>
                </div>
                <div class="panel stack-form">
                    <h2>Gambar sampul</h2>
                    <label class="upload-zone" for="gambar">
                        <span aria-hidden="true">↑</span>
                        <strong>Pilih gambar</strong>
                        <small>JPG/JPEG, PNG, WebP · maks. 2 MB<br>Maks. 5000 × 5000 piksel</small>
                    </label>
                    <input
                        id="gambar"
                        name="gambar"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        data-max-bytes="2097152"
                        data-max-width="5000"
                        data-max-height="5000"
                        aria-describedby="image-help image-error"
                    >
                    <small
                        id="image-error"
                        class="text-danger"
                        role="status"
                        @if (!$errors->has('gambar')) hidden @endif
                    >
                        {{ $errors->first('gambar') }}
                    </small>
                    <small id="image-help">
                        Opsional. Gambar draft hanya dapat dilihat oleh Anda.
                    </small>
                    <img
                        id="image-preview"
                        class="image-preview"
                        alt="Pratinjau sampul"
                        @if ($article->gambar) src="{{ route('article.image', $article) }}" @else hidden @endif
                    >
                    @if ($article->gambar)
                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                name="remove_image"
                                value="1"
                            >
                            Hapus gambar saat disimpan
                        </label>
                    @endif
                </div>
                <button class="btn btn-full" type="submit">
                    {{ $article->exists ? 'Simpan perubahan' : 'Simpan artikel' }}
                </button>
                <a class="btn btn-full btn-outline" href="{{ route('articles.index') }}">
                    Batal
                </a>
            </aside>
        </form>
    </section>
@endsection
