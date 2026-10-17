<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $article = $this->route('article');

        return $this->user() && (! $article || $this->user()->can('update', $article));
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'min:5', 'max:180'],
            'isi' => ['required', 'string', 'min:30', 'max:50000'],
            'status' => ['required', 'boolean'],
            'kategori' => ['required', Rule::in(Article::CATEGORIES)],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=5000,max_height=5000'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['judul' => 'judul', 'isi' => 'isi artikel', 'gambar' => 'gambar', 'kategori' => 'kategori'];
    }

    public function messages(): array
    {
        $file = $this->file('gambar');
        $uploadMessage = match ($file instanceof UploadedFile ? $file->getError() : null) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Gambar gagal diunggah. Gunakan JPG/JPEG, PNG, atau WebP maksimal 2 MB, lalu pilih ulang file.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Server belum dapat menerima gambar. Coba lagi nanti atau hubungi pengelola aplikasi.',
            UPLOAD_ERR_PARTIAL => 'Unggahan gambar belum selesai. Pilih ulang file dan coba lagi.',
            default => 'Gambar gagal diunggah. Pilih ulang file JPG/JPEG, PNG, atau WebP yang valid.',
        };

        return [
            'gambar.uploaded' => $uploadMessage,
            'gambar.max' => 'Ukuran gambar maksimal 2 MB. Kompres gambar atau pilih file yang lebih kecil.',
            'gambar.dimensions' => 'Dimensi gambar maksimal 5000 × 5000 piksel. Perkecil gambar sebelum mengunggah.',
        ];
    }
}
