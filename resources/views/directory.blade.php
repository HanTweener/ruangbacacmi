@extends('layouts.app')
@section('title', 'Direktori Artikel')
@section('content')
    <section class="container workspace">
        <div class="page-heading">
            <div>
                <span class="eyebrow">
                    ARTIKEL & PENULIS
                </span>
                <h1>Direktori artikel<span>.</span></h1>
                <p>Daftar artikel Published terbaru beserta informasi penulis.</p>
            </div>
            <span class="count-chip">
                {{ $articles->total() }} artikel
            </span>
        </div>
        <form class="filter-bar" method="get">
            <div class="grow">
                <label for="directory-search">
                    Cari judul, nama, atau email penulis
                </label>
                <input
                    id="directory-search"
                    name="q"
                    type="search"
                    maxlength="100"
                    value="{{ request('q') }}"
                    placeholder="Temukan artikel atau penulis..."
                >
            </div>
            <button class="btn" type="submit">
                Cari artikel ↗
            </button>
            @if (request()->filled('q'))
                <a href="{{ route('directory') }}">
                    Reset
                </a>
            @endif
        </form>
        <div class="panel">
            @if ($articles->isEmpty())
                <div class="empty-state">
                    <h2>Tidak ada artikel ditemukan.</h2>
                    <p>Coba kata kunci lain untuk menemukan bacaan.</p>
                </div>
            @else
                <div
                    class="table-scroll"
                    tabindex="0"
                    role="region"
                    aria-label="Direktori artikel dan penulis"
                >
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Judul artikel</th>
                                <th scope="col">Nama penulis</th>
                                <th scope="col">Email penulis</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tanggal dibuat ↓</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>
                                        <a class="table-title" href="{{ route('public.show', $article->slug) }}">
                                            {{ $article->judul }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $article->author_name }}
                                    </td>
                                    <td>
                                        {{ $article->author_email }}
                                    </td>
                                    <td>
                                        <span class="badge published">
                                            Published
                                        </span>
                                    </td>
                                    <td class="nowrap">
                                        {{ \Illuminate\Support\Carbon::parse($article->created_at)->translatedFormat('d M Y, H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        {{ $articles->links() }}
        <p class="muted small">Menampilkan maksimal 10 artikel per halaman. Direktori penulis hanya tersedia bagi pengguna
            yang sudah masuk.</p>
    </section>
@endsection
