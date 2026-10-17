<article class="article-card">
    <a
        class="card-cover cover-{{ $article->id % 3 }}"
        href="{{ route('public.show', $article) }}"
        tabindex="-1"
        aria-hidden="true"
    >
        <img
            src="{{ $article->gambar ? route('article.image', $article) : asset('assets/editorial-' . $article->id % 3 . '.svg') }}"
            alt=""
            loading="lazy"
            width="640"
            height="420"
        >
    </a>
    <div class="card-meta">
        <span class="category">
            {{ $article->kategori }}
        </span>
    </div>
    <h3><a href="{{ route('public.show', $article) }}">{{ $article->judul }}</a></h3>
    <p>{{ Str::limit($article->isi, 115) }}</p>
    <div class="card-byline">
        <span class="avatar">
            {{ mb_substr($article->user->name, 0, 1) }}
        </span>
        <span>
            {{ $article->user->name }}
        </span>
        <span class="byline-date">
            {{ $article->created_at->translatedFormat('d M Y') }}
        </span>
    </div>
</article>
