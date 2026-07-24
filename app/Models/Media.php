<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Catatan satu berkas gambar yang diunggah admin. */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['name', 'path', 'mime', 'size', 'alt', 'user_id'];

    protected $appends = ['url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): ?string
    {
        return MediaUrl::resolve($this->path);
    }
}
