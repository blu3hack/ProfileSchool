<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use Illuminate\Database\Eloquent\Model;

/** Baris kontak di footer (alamat, telepon, email, jam operasional). */
class ContactInfo extends Model
{
    use Orderable;

    protected $fillable = ['icon', 'label', 'value', 'href', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
