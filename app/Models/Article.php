<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    public const CATEGORIES = ['Kesehatan', 'Gaya Hidup', 'Nutrisi', 'Kegiatan'];

    protected $fillable = ['judul', 'slug', 'isi', 'status', 'kategori', 'gambar'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 1)->whereHas('user');
    }
}
