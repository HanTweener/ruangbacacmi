<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageValidationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(UploadedFile $image): array
    {
        return ['judul' => 'Synthetic upload validation', 'isi' => str_repeat('Synthetic article content. ', 3), 'kategori' => 'Kesehatan', 'status' => 0, 'gambar' => $image];
    }

    public function test_jpeg_extension_is_accepted_stored_and_served_as_jpeg(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $this->get('/articles/create')->assertOk()->assertSee('JPG/JPEG')->assertSee('.jpg,.jpeg,.png,.webp', false);
        $this->post('/articles', $this->payload(UploadedFile::fake()->image('cover.jpeg', 800, 600)))
            ->assertRedirect('/articles')->assertSessionHasNoErrors();
        $article = Article::sole();
        Storage::disk('local')->assertExists($article->gambar);
        $this->get(route('article.image', $article))->assertOk()->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_php_rejected_upload_has_actionable_indonesian_feedback_and_preserves_text(): void
    {
        $file = UploadedFile::fake()->create('synthetic.jpg');
        $rejected = new UploadedFile($file->getPathname(), 'synthetic.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);
        $this->actingAs(User::factory()->create())->from('/articles/create')
            ->post('/articles', $this->payload($rejected))
            ->assertRedirect('/articles/create')
            ->assertSessionHasErrors(['gambar' => 'Gambar gagal diunggah. Gunakan JPG/JPEG, PNG, atau WebP maksimal 2 MB, lalu pilih ulang file.'])
            ->assertSessionHasInput('judul', 'Synthetic upload validation');
        $this->assertDatabaseCount('articles', 0);
    }

    public function test_oversized_and_overdimensioned_images_explain_the_actual_limits(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/articles', $this->payload(UploadedFile::fake()->image('large.jpg')->size(3636)))
            ->assertSessionHasErrors(['gambar' => 'Ukuran gambar maksimal 2 MB. Kompres gambar atau pilih file yang lebih kecil.']);
        $this->post('/articles', $this->payload(UploadedFile::fake()->image('wide.png', 5001, 1)))
            ->assertSessionHasErrors(['gambar' => 'Dimensi gambar maksimal 5000 × 5000 piksel. Perkecil gambar sebelum mengunggah.']);
        $this->assertDatabaseCount('articles', 0);
    }

    public function test_server_temp_failures_do_not_blame_the_image_size_or_format(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE] as $error) {
            $file = UploadedFile::fake()->create('synthetic.jpeg');
            $rejected = new UploadedFile($file->getPathname(), 'synthetic.jpeg', 'image/jpeg', $error, true);
            $this->post('/articles', $this->payload($rejected))
                ->assertSessionHasErrors(['gambar' => 'Server belum dapat menerima gambar. Coba lagi nanti atau hubungi pengelola aplikasi.']);
        }
        $this->assertDatabaseCount('articles', 0);
    }
}
