<?php

namespace App\Support;

use App\Models\ContactInfo;
use App\Models\NavLink;
use App\Models\SocialLink;
use Illuminate\Support\Facades\Schema;

/**
 * Identitas & data kontak sekolah yang dipakai bersama oleh navbar/footer
 * di semua halaman (landing, indeks berita, detail berita).
 *
 * Seluruh datanya kini berasal dari database dan dikelola lewat panel
 * admin. Nilai bawaan di bawah hanya jaring pengaman bila tabelnya belum
 * dimigrasikan atau masih kosong.
 */
class SiteInfo
{
    public static function name(): string
    {
        return PageContent::get('school_name') ?: config('app.name', 'Alazka Islamic School');
    }

    /** Tautan navigasi utama. Hash mengacu ke section di landing page. */
    public static function navLinks(): array
    {
        $links = self::fromTable('nav_links', fn () => NavLink::active()->ordered()
            ->get()
            ->map(fn (NavLink $link) => ['label' => $link->label, 'hash' => $link->hash])
            ->all());

        return $links ?: self::defaultNavLinks();
    }

    public static function contacts(): array
    {
        $contacts = self::fromTable('contact_infos', fn () => ContactInfo::active()->ordered()
            ->get()
            ->map(fn (ContactInfo $c) => [
                'icon' => $c->icon,
                'label' => $c->label,
                'value' => $c->value,
                'href' => $c->href ?: null,
            ])
            ->all());

        return $contacts ?: self::defaultContacts();
    }

    public static function socials(): array
    {
        $socials = self::fromTable('social_links', fn () => SocialLink::active()->ordered()
            ->get()
            ->map(fn (SocialLink $s) => ['label' => $s->label, 'href' => $s->href])
            ->all());

        return $socials ?: self::defaultSocials();
    }

    /** Menjalankan query hanya bila tabelnya sudah ada. */
    protected static function fromTable(string $table, callable $query): array
    {
        return Schema::hasTable($table) ? $query() : [];
    }

    protected static function defaultNavLinks(): array
    {
        return [
            ['label' => 'Beranda', 'hash' => '#beranda'],
            ['label' => 'Keunggulan', 'hash' => '#keunggulan'],
            ['label' => 'Berita', 'hash' => '#berita'],
            ['label' => 'Kegiatan', 'hash' => '#kegiatan'],
            ['label' => 'Prestasi', 'hash' => '#prestasi'],
            ['label' => 'Kontak', 'hash' => '#kontak'],
        ];
    }

    protected static function defaultContacts(): array
    {
        return [
            ['icon' => '📍', 'label' => 'Alamat', 'value' => 'Jl. Pendidikan Islami No. 45, Cimahi Utara, Jawa Barat 40514', 'href' => null],
            ['icon' => '📞', 'label' => 'Telepon', 'value' => '085 606 000 606', 'href' => 'tel:+6285606000606'],
            ['icon' => '✉️', 'label' => 'Email', 'value' => 'info@alazka.sch.id', 'href' => 'mailto:info@alazka.sch.id'],
            ['icon' => '🕐', 'label' => 'Jam Operasional', 'value' => 'Senin – Jumat, 07.00 – 16.00 WIB', 'href' => null],
        ];
    }

    protected static function defaultSocials(): array
    {
        return [
            ['label' => 'Instagram', 'href' => '#'],
            ['label' => 'YouTube', 'href' => '#'],
            ['label' => 'Facebook', 'href' => '#'],
            ['label' => 'WhatsApp', 'href' => 'https://wa.me/6285606000606'],
        ];
    }
}
