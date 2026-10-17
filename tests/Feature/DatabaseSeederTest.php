<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_articles_have_distinct_bodies_and_card_excerpts(): void
    {
        $this->seed();

        $published = Article::where('status', true)->get();

        $this->assertCount(12, $published);
        $this->assertCount(12, $published->pluck('isi')->unique());
        $this->assertCount(12, $published->map(fn (Article $article) => Str::limit($article->isi, 115))->unique());
        $this->assertDatabaseCount('articles', 13);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_reseeding_preserves_existing_edits_and_account_passwords(): void
    {
        $this->seed();
        $article = Article::where('status', true)->firstOrFail();
        $article->update(['isi' => 'Konten yang sudah disunting oleh pemilik artikel.']);
        $passwords = User::pluck('password', 'id')->all();

        $this->seed();

        $this->assertSame('Konten yang sudah disunting oleh pemilik artikel.', $article->fresh()->isi);
        $this->assertSame($passwords, User::pluck('password', 'id')->all());
        $this->assertDatabaseCount('articles', 13);
        $this->assertDatabaseCount('users', 2);
    }
}
