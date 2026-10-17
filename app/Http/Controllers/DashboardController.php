<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->user()->articles();
        $total = (clone $query)->count();
        $published = (clone $query)->where('status', 1)->count();
        $draft = $total - $published;
        $articles = $query->latest()->orderByDesc('id')->limit(5)->get();

        return view('dashboard', compact('total', 'published', 'draft', 'articles'));
    }
}
