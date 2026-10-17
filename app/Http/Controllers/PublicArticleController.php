<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PublicArticleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'kategori' => ['nullable', Rule::in(Article::CATEGORIES)]]);
        $query = Article::published()->with('user:id,name');
        if ($request->filled('q')) {
            $term = $filters['q'];
            $query->where(fn ($q) => $q->where('judul', 'like', '%'.$term.'%')->orWhere('isi', 'like', '%'.$term.'%'));
        }
        $query->when($filters['kategori'] ?? null, fn ($query, $category) => $query->where('kategori', $category));
        $articles = $query->latest()->orderByDesc('id')->paginate(9)->withQueryString();

        return view('public.index', compact('articles'));
    }

    public function show(Article $article)
    {
        abort_unless($article->status && $article->user()->exists(), 404);
        $article->load('user:id,name');
        $related = Article::published()->with('user:id,name')->whereKeyNot($article->id)->latest()->limit(3)->get();

        return view('public.show', compact('article', 'related'));
    }

    public function image(Article $article)
    {
        abort_unless($article->status || (auth()->check() && auth()->id() === $article->user_id), 404);
        abort_unless($article->gambar && Storage::disk('local')->exists($article->gambar), 404);

        return Storage::disk('local')->response($article->gambar, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
