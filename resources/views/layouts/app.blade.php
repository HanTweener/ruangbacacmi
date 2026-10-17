<!doctype html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description"
            content="Ruang Baca CMI: ruang untuk berbagi inspirasi dan pengetahuan kesehatan. Proyek demonstrasi tes rekrutmen."
        >
        <title>@yield('title', 'Ruang Baca') · Klinik Utama CMI</title>
        <link
            rel="icon"
            href="{{ asset('assets/favicon.svg') }}"
            type="image/svg+xml"
        >
        <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
        <script src="{{ asset('assets/app.js') }}" defer></script>
    </head>

    <body>
        <a class="skip-link" href="#main">
            Lewati ke konten
        </a>
        <header class="site-header">
            <div class="container header-inner">
                <div class="brand">
                    <a
                        class="brand-logo"
                        href="https://www.cmihospital.com/"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Website Klinik Utama CMI"
                    >
                        <img
                            src="{{ asset('assets/cmi-logo.webp') }}"
                            alt="Klinik Utama CMI"
                            width="105"
                            height="48"
                        >
                    </a>
                    <span class="brand-divider" aria-hidden="true"></span>
                    <a
                        class="brand-name"
                        href="{{ route('home') }}"
                        aria-label="Ruang Baca - Beranda"
                    >ruang<span>baca.</span></a>
                </div>
                <button
                    class="menu-toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="main-nav"
                >
                    Menu <span aria-hidden="true">☰</span>
                </button>
                <nav id="main-nav" aria-label="Navigasi utama">
                    <a class="{{ request()->routeIs('home', 'public.show') ? 'active' : '' }}"
                        href="{{ route('home') }}"
                    >
                        Jelajahi artikel
                    </a>
                    @auth
                        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            Dashboard
                        </a>
                        <a class="{{ request()->routeIs('articles.*') ? 'active' : '' }}"
                            href="{{ route('articles.index') }}"
                        >
                            Artikel saya
                        </a>
                        <a class="{{ request()->routeIs('directory') ? 'active' : '' }}" href="{{ route('directory') }}">
                            Direktori
                        </a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-small btn-outline" type="submit">
                                Keluar <span aria-hidden="true">↗</span>
                            </button>
                        </form>
                    @else
                        <a class="btn btn-small" href="{{ route('login') }}">
                            Mulai menulis <span aria-hidden="true">↗</span>
                        </a>
                    @endauth
                </nav>
            </div>
        </header>
        <main id="main">
            @if (session('success'))
                <div class="container">
                    <div class="alert success" role="status">
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="container">
                    <div class="alert error" role="alert">
                        <strong>
                            Periksa kembali isian Anda.
                        </strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            @yield('content')
        </main>
        <footer class="site-footer">
            <div class="container footer-top">
                <div>
                    <a class="footer-brand" href="{{ route('home') }}">
                        ruangbaca<span>.</span>
                    </a>
                    <p>Artikel kesehatan, gaya hidup,<br>nutrisi, dan kegiatan.</p>
                </div>
                <div>
                    <span class="eyebrow">
                        UNTUK PENULIS
                    </span>
                    <p>Tulis dan kelola artikel<br>melalui akun penulis.</p>
                </div>
                <a
                    class="footer-link"
                    href="https://www.cmihospital.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Kenali Klinik Utama CMI ↗
                </a>
            </div>
            <div class="container footer-bottom">
                <span>
                    © {{ date('Y') }} Ruang Baca CMI
                </span>
                <span>
                    Demo tes rekrutmen · Bukan situs resmi atau saran medis
                </span>
            </div>
        </footer>
        <dialog id="delete-dialog">
            <form method="dialog">
                <button
                    class="dialog-close"
                    aria-label="Tutup"
                    value="cancel"
                >
                    ×
                </button>
                <span class="eyebrow">
                    KONFIRMASI
                </span>
                <h2>Hapus artikel ini?</h2>
                <p>Artikel dan gambarnya akan dihapus permanen.</p>
                <div class="actions">
                    <button class="btn btn-danger" value="confirm">
                        Ya, hapus artikel
                    </button>
                    <button class="btn btn-outline" value="cancel">
                        Batal
                    </button>
                </div>
            </form>
        </dialog>
    </body>

</html>
