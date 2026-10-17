<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:0,1']]);
        $query = $request->user()->articles()->latest()->orderByDesc('id');
        if ($request->filled('q')) {
            $query->where('judul', 'like', '%'.$filters['q'].'%');
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $articles = $query->paginate(10)->withQueryString();

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        return view('articles.form', ['article' => new Article]);
    }

    public function store(ArticleRequest $request)
    {
        $data = $request->safe()->except(['gambar', 'remove_image']);
        $data['slug'] = Str::slug($data['judul']).'-'.Str::uuid();
        $image = $request->hasFile('gambar') ? $request->file('gambar')->store('articles', 'local') : null;
        try {
            $request->user()->articles()->create([...$data, 'gambar' => $image]);
        } catch (\Throwable $exception) {
            if ($image) {
                Storage::disk('local')->delete($image);
            }
            throw $exception;
        }

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil disimpan.');
    }

    public function show(Article $article)
    {
        Gate::authorize('update', $article);
        $article->load('user');

        return view('articles.show', compact('article'));
    }

    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        return view('articles.form', compact('article'));
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $data = $request->safe()->except(['gambar', 'remove_image']);
        $oldImage = $article->gambar;
        $newImage = $request->hasFile('gambar') ? $request->file('gambar')->store('articles', 'local') : null;
        if ($newImage || $request->boolean('remove_image')) {
            $data['gambar'] = $newImage;
        }
        try {
            $article->update($data);
        } catch (\Throwable $exception) {
            if ($newImage) {
                Storage::disk('local')->delete($newImage);
            }
            throw $exception;
        }
        if (array_key_exists('gambar', $data) && $oldImage) {
            Storage::disk('local')->delete($oldImage);
        }

        return redirect()->route('articles.index')->with('success', 'Perubahan artikel berhasil disimpan.');
    }

    public function destroy(Article $article)
    {
        Gate::authorize('delete', $article);
        $image = $article->gambar;
        $article->delete();
        if ($image) {
            Storage::disk('local')->delete($image);
        }

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil dihapus.');
    }
}
