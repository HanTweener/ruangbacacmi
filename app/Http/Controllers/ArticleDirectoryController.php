<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleDirectoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        // Question 2: join() produces an explicit INNER JOIN.
        $articles = DB::table('articles')
            ->join('users', 'articles.user_id', '=', 'users.id')
            ->select('articles.judul', 'articles.slug', 'users.name as author_name', 'users.email as author_email', 'articles.status', 'articles.created_at')
            ->where('articles.status', 1)
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($search) use ($request) {
                    $term = '%'.$request->string('q')->toString().'%';
                    $search->where('articles.judul', 'like', $term)->orWhere('users.name', 'like', $term)->orWhere('users.email', 'like', $term);
                });
            })
            ->orderByDesc('articles.created_at')->orderByDesc('articles.id')
            ->paginate(10)->withQueryString();

        return view('directory', compact('articles'));
    }
}
