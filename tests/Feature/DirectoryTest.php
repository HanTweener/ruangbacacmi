<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inner_join_published_filter_order_columns_and_ten_row_pagination(): void
    {
        $author = User::factory()->create();
        for ($i = 0; $i < 12; $i++) {
            Article::factory()->for($author)->create(['status' => true, 'judul' => 'Article '.$i, 'created_at' => now()->subHours($i)]);
        }
        Article::factory()->for($author)->create(['judul' => 'Hidden draft', 'status' => false]);
        DB::enableQueryLog();
        $response = $this->actingAs($author)->get('/daftar-artikel');
        $response->assertOk()->assertSee($author->name)->assertSee($author->email)->assertDontSee('Hidden draft')->assertViewHas('articles', fn ($rows) => $rows->count() === 10 && $rows->total() === 12 && $rows->first()->judul === 'Article 0');
        $sql = collect(DB::getQueryLog())->pluck('query')->implode("\n");
        $this->assertStringContainsString('inner join', strtolower($sql));
        $this->assertStringContainsString('created_at', $sql);
        $this->get('/daftar-artikel?page=2')->assertViewHas('articles', fn ($rows) => $rows->count() === 2 && $rows->first()->judul === 'Article 10');
    }

    public function test_search_by_title_author_email_does_not_bypass_status(): void
    {
        $author = User::factory()->create(['name' => 'Synthetic Writer', 'email' => 'writer@example.test']);
        Article::factory()->for($author)->create(['status' => true, 'judul' => 'Jurnal kebiasaan harian']);
        Article::factory()->for($author)->create(['status' => false, 'judul' => 'Private Synthetic Draft']);
        $this->actingAs($author);
        foreach (['Jurnal', 'Synthetic', 'writer@example.test'] as $term) {
            $this->get('/daftar-artikel?q='.urlencode($term))->assertOk()->assertViewHas('articles', fn ($rows) => $rows->total() === 1)->assertDontSee('Private Synthetic Draft');
        }
        $this->get('/daftar-artikel?q=%27%20OR%201%3D1')->assertViewHas('articles', fn ($rows) => $rows->total() === 0);
    }

    public function test_inner_join_excludes_orphaned_article(): void
    {
        $user = User::factory()->create();
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } else {
            // RefreshDatabase wraps SQLite in a transaction; defer the FK check
            // until commit, then remove this deliberately invalid fixture first.
            DB::statement('PRAGMA defer_foreign_keys = ON');
        }
        try {
            DB::table('articles')->insert(['user_id' => 999999, 'judul' => 'Orphan record', 'slug' => 'orphan', 'isi' => 'Synthetic orphan fixture', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        } finally {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
        $this->actingAs($user)->get('/daftar-artikel')->assertOk()->assertDontSee('Orphan record')->assertViewHas('articles', fn ($rows) => $rows->total() === 0);
        DB::table('articles')->where('slug', 'orphan')->delete();
    }

    public function test_join_search_pagination_preserves_query(): void
    {
        $author = User::factory()->create(['name' => 'Directory Author']);
        Article::factory()->count(11)->for($author)->create(['status' => true]);
        $this->actingAs($author)->get('/daftar-artikel?q=Directory')->assertSee('q=Directory', false)->assertSee('page=2', false);
        $this->get('/daftar-artikel?q=Directory&page=2')->assertViewHas('articles', fn ($rows) => $rows->count() === 1);
    }
}
