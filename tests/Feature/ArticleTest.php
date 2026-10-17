<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return [...['judul' => 'Cerita demonstrasi yang baru', 'isi' => str_repeat('Konten sintetis untuk pengujian artikel. ', 4), 'kategori' => 'Kesehatan', 'status' => 0], ...$overrides];
    }

    public function test_guests_see_only_published_content_and_cannot_enter_protected_routes(): void
    {
        $public = Article::factory()->create(['status' => true]);
        $draft = Article::factory()->create(['judul' => 'Draft rahasia sintetis']);
        $this->get('/')->assertOk()->assertSee($public->judul)->assertDontSee($draft->judul)->assertDontSee($public->user->email);
        $this->get(route('public.show', $public))->assertOk();
        $this->get(route('public.show', $draft))->assertNotFound();
        foreach (['/dashboard', '/articles', '/articles/create', '/daftar-artikel'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
        $this->post('/articles', $this->payload())->assertRedirect('/login');
        $this->put(route('articles.update', $public), $this->payload())->assertRedirect('/login');
        $this->delete(route('articles.destroy', $public))->assertRedirect('/login');
    }

    public function test_owner_can_create_read_update_delete_and_cannot_spoof_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($owner)->post('/articles', $this->payload(['user_id' => $other->id, 'slug' => 'spoof']))->assertRedirect('/articles');
        $article = Article::firstOrFail();
        $this->assertSame($owner->id, $article->user_id);
        $this->assertNotSame('spoof', $article->slug);
        $this->get(route('articles.show', $article))->assertOk();
        $this->get(route('articles.edit', $article))->assertOk();
        $this->put(route('articles.update', $article), $this->payload(['status' => 1, 'judul' => 'Judul hasil perubahan']))->assertRedirect('/articles');
        $this->get(route('public.show', $article->fresh()))->assertOk()->assertSee('Judul hasil perubahan');
        $this->delete(route('articles.destroy', $article))->assertRedirect('/articles');
        $this->assertDatabaseCount('articles', 0);
    }

    public function test_foreign_author_cannot_read_preview_edit_update_delete_or_see_counts(): void
    {
        $article = Article::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($other);
        $this->get(route('articles.show', $article))->assertForbidden();
        $this->get(route('articles.edit', $article))->assertForbidden();
        $this->put(route('articles.update', $article), $this->payload())->assertForbidden();
        $this->delete(route('articles.destroy', $article))->assertForbidden();
        $this->get('/articles')->assertDontSee($article->judul);
        $this->get('/dashboard')->assertViewHas('total', 0)->assertViewHas('published', 0)->assertViewHas('draft', 0);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'user_id' => $article->user_id]);
    }

    public function test_dashboard_counts_and_latest_articles_are_owner_scoped(): void
    {
        $owner = User::factory()->create();
        Article::factory()->count(2)->for($owner)->create(['status' => true]);
        Article::factory()->for($owner)->create();
        Article::factory()->create(['status' => true]);
        $this->actingAs($owner)->get('/dashboard')->assertOk()->assertViewHas('total', 3)->assertViewHas('published', 2)->assertViewHas('draft', 1)->assertViewHas('articles', fn ($rows) => $rows->count() === 3);
    }

    public function test_validation_rejects_invalid_content_and_unsafe_uploads(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $this->post('/articles', $this->payload(['judul' => 'a', 'isi' => 'x', 'status' => 9, 'kategori' => 'invalid']))->assertSessionHasErrors(['judul', 'isi', 'status', 'kategori']);
        $this->post('/articles', $this->payload(['gambar' => UploadedFile::fake()->create('script.php', 10, 'text/plain')]))->assertSessionHasErrors('gambar');
        $this->post('/articles', $this->payload(['gambar' => UploadedFile::fake()->createWithContent('evil.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')]))->assertSessionHasErrors('gambar');
        $this->post('/articles', $this->payload(['gambar' => UploadedFile::fake()->image('too-big.jpg')->size(2049)]))->assertSessionHasErrors('gambar');
        $this->assertDatabaseCount('articles', 0);
    }

    public function test_dashboard_returns_five_latest_owned_articles_in_order(): void
    {
        $owner = User::factory()->create();
        for ($index = 0; $index < 7; $index++) {
            Article::factory()->for($owner)->create(['judul' => 'Owned '.$index, 'created_at' => now()->subHours($index)]);
        }
        Article::factory()->create(['judul' => 'Another author latest']);
        $this->actingAs($owner)->get('/dashboard')->assertViewHas('articles', fn ($rows) => $rows->pluck('judul')->all() === ['Owned 0', 'Owned 1', 'Owned 2', 'Owned 3', 'Owned 4']);
    }

    public function test_failed_image_storage_does_not_create_article(): void
    {
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('putFileAs')->once()->andThrow(new \RuntimeException('Synthetic storage failure'));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs(User::factory()->create())->post('/articles', $this->payload(['gambar' => UploadedFile::fake()->image('synthetic.png')]));
            $this->fail('Expected storage failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Synthetic storage failure', $exception->getMessage());
        }
        $this->assertDatabaseCount('articles', 0);
    }

    public function test_image_upload_privacy_replacement_removal_and_article_deletion(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/articles', $this->payload(['gambar' => UploadedFile::fake()->image('cover.png')]))->assertRedirect('/articles');
        $article = Article::firstOrFail();
        $old = $article->gambar;
        Storage::disk('local')->assertExists($old);
        $this->get(route('article.image', $article))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('article.image', $article))->assertNotFound();
        auth()->logout();
        $this->get(route('article.image', $article))->assertNotFound();
        $this->actingAs($owner)->put(route('articles.update', $article), $this->payload(['gambar' => UploadedFile::fake()->image('new.jpg'), 'status' => 1]))->assertRedirect('/articles');
        Storage::disk('local')->assertMissing($old);
        $new = $article->fresh()->gambar;
        Storage::disk('local')->assertExists($new);
        auth()->logout();
        $this->get(route('article.image', $article))->assertOk();
        $this->actingAs($owner)->put(route('articles.update', $article), $this->payload(['remove_image' => 1]))->assertRedirect('/articles');
        Storage::disk('local')->assertMissing($new);
        $this->assertNull($article->fresh()->gambar);
        $this->put(route('articles.update', $article), $this->payload(['gambar' => UploadedFile::fake()->image('last.png')]))->assertRedirect('/articles');
        $last = $article->fresh()->gambar;
        $this->delete(route('articles.destroy', $article))->assertRedirect('/articles');
        Storage::disk('local')->assertMissing($last);
    }

    public function test_slug_is_unique_and_stable_and_html_is_escaped(): void
    {
        $this->actingAs(User::factory()->create());
        for ($i = 0; $i < 2; $i++) {
            $this->post('/articles', $this->payload(['status' => 1, 'isi' => '<script>alert("xss")</script>'.str_repeat(' safe text', 10)]))->assertRedirect('/articles');
        }
        $this->assertSame(2, Article::distinct()->count('slug'));
        $article = Article::first();
        $slug = $article->slug;
        $this->get(route('public.show', $article))->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(', false);
        $this->put(route('articles.update', $article), $this->payload(['judul' => 'Judul berbeda sepenuhnya']))->assertRedirect('/articles');
        $this->assertSame($slug, $article->fresh()->slug);
    }

    public function test_public_search_category_and_pagination_preserve_filters(): void
    {
        Article::factory()->count(10)->create(['status' => true, 'judul' => 'Bacaan sehat', 'kategori' => 'Nutrisi']);
        Article::factory()->create(['judul' => 'Bacaan sehat', 'kategori' => 'Nutrisi']);
        $this->get('/?q=Bacaan&kategori=Nutrisi')->assertOk()->assertViewHas('articles', fn ($rows) => $rows->count() === 9 && $rows->total() === 10)->assertSee('page=2', false);
        $this->get('/?q=Bacaan&kategori=Nutrisi&page=2')->assertViewHas('articles', fn ($rows) => $rows->count() === 1);
        $this->get('/?q=does-not-exist')->assertSee('Belum ada bacaan');
    }

    public function test_missing_binding_and_invalid_search_are_handled(): void
    {
        $this->get('/artikel/missing')->assertNotFound();
        $this->get('/?q[]=invalid')->assertRedirect()->assertSessionHasErrors('q');
    }

    public function test_zero_search_is_a_real_search_for_public_and_owner_lists(): void
    {
        $owner = User::factory()->create();
        Article::factory()->for($owner)->create(['status' => true, 'judul' => 'Story 0', 'isi' => 'Matching story text']);
        Article::factory()->for($owner)->create(['status' => true, 'judul' => 'Story A', 'isi' => 'Different story text']);
        $this->get('/?q=0')->assertViewHas('articles', fn ($rows) => $rows->total() === 1);
        $this->actingAs($owner)->get('/articles?q=0')->assertViewHas('articles', fn ($rows) => $rows->total() === 1);
    }
}
