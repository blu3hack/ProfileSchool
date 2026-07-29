<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Activity;
use App\Models\ContactInfo;
use App\Models\Event;
use App\Models\GalleryImage;
use App\Models\HeroSlide;
use App\Models\NavLink;
use App\Models\News;
use App\Models\Pillar;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use App\Models\Stat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Mengisi database dengan konten awal — persis konten yang dulu
 * di-hardcode di controller & repository.
 *
 * Aman dijalankan berulang: semua isian memakai `updateOrCreate`
 * sehingga tidak menghasilkan duplikat dan tidak menimpa data yang
 * sudah diedit admin (kecuali baris dengan kunci yang sama).
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedNavLinks();
        $this->seedHeroSlides();
        $this->seedStats();
        $this->seedPillars();
        $this->seedActivities();
        $this->seedAchievements();
        $this->seedGallery();
        $this->seedContacts();
        $this->seedSocials();
        $this->seedNews();
        $this->seedEvents();

        SiteSetting::flush();
    }

    /** Teks halaman: definisi + default diambil dari config/site_content.php. */
    protected function seedSettings(): void
    {
        foreach (config('site_content.fields', []) as $index => $field) {
            $attributes = [
                'group' => $field['group'] ?? 'umum',
                'type' => $field['type'] ?? 'text',
                'label' => $field['label'],
                'hint' => $field['hint'] ?? null,
                'sort_order' => $index,
            ];

            // Nilai hanya diisi saat baris baru dibuat. Baris yang sudah ada
            // dibiarkan apa adanya — termasuk yang sengaja dikosongkan admin,
            // supaya seeding ulang tidak menghidupkan kembali teks default.
            if (! SiteSetting::where('key', $field['key'])->exists()) {
                $attributes['value'] = $field['default'] ?? null;
            }

            SiteSetting::updateOrCreate(['key' => $field['key']], $attributes);
        }
    }

    protected function seedNavLinks(): void
    {
        $links = [
            ['label' => 'Beranda', 'hash' => '#beranda'],
            ['label' => 'Keunggulan', 'hash' => '#keunggulan'],
            ['label' => 'Berita', 'hash' => '#berita'],
            ['label' => 'Next Event', 'hash' => '#event'],
            ['label' => 'Kegiatan', 'hash' => '#kegiatan'],
            ['label' => 'Prestasi', 'hash' => '#prestasi'],
            ['label' => 'Galeri', 'hash' => '#galeri'],
            ['label' => 'Kontak', 'hash' => '#kontak'],
        ];

        foreach ($links as $i => $link) {
            NavLink::updateOrCreate(['hash' => $link['hash']], [...$link, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedHeroSlides(): void
    {
        foreach ($this->data('hero_slides') as $i => $row) {
            HeroSlide::updateOrCreate(['title' => $row['title']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedStats(): void
    {
        foreach ($this->data('stats') as $i => $row) {
            Stat::updateOrCreate(['label' => $row['label']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedPillars(): void
    {
        foreach ($this->data('pillars') as $i => $row) {
            Pillar::updateOrCreate(['title' => $row['title']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedActivities(): void
    {
        foreach ($this->data('activities') as $i => $row) {
            Activity::updateOrCreate(['title' => $row['title']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedAchievements(): void
    {
        foreach ($this->data('achievements') as $i => $row) {
            Achievement::updateOrCreate(['title' => $row['title']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedGallery(): void
    {
        foreach ($this->data('gallery') as $i => $row) {
            GalleryImage::updateOrCreate(['title' => $row['title']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedContacts(): void
    {
        $contacts = [
            ['icon' => '📍', 'label' => 'Alamat', 'value' => 'Jl. Pendidikan Islami No. 45, Cimahi Utara, Jawa Barat 40514', 'href' => null],
            ['icon' => '📞', 'label' => 'Telepon', 'value' => '085 606 000 606', 'href' => 'tel:+6285606000606'],
            ['icon' => '✉️', 'label' => 'Email', 'value' => 'info@alazka.sch.id', 'href' => 'mailto:info@alazka.sch.id'],
            ['icon' => '🕐', 'label' => 'Jam Operasional', 'value' => 'Senin – Jumat, 07.00 – 16.00 WIB', 'href' => null],
        ];

        foreach ($contacts as $i => $row) {
            ContactInfo::updateOrCreate(['label' => $row['label']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedSocials(): void
    {
        $socials = [
            ['label' => 'Instagram', 'href' => 'https://instagram.com/'],
            ['label' => 'YouTube', 'href' => 'https://youtube.com/'],
            ['label' => 'Facebook', 'href' => 'https://facebook.com/'],
            ['label' => 'WhatsApp', 'href' => 'https://wa.me/6285606000606'],
        ];

        foreach ($socials as $i => $row) {
            SocialLink::updateOrCreate(['label' => $row['label']], [...$row, 'sort_order' => $i, 'is_active' => true]);
        }
    }

    protected function seedNews(): void
    {
        foreach ($this->data('news') as $article) {
            News::updateOrCreate(['slug' => $article['slug']], [
                'title' => $article['title'],
                'category' => $article['category'],
                'icon' => $article['icon'],
                'accent' => $article['accent'],
                'excerpt' => $article['excerpt'],
                'author' => $article['author'],
                'read_time' => $article['readTime'],
                'image' => $article['image'],
                'image_caption' => $article['imageCaption'] ?? null,
                'tags' => $article['tags'] ?? [],
                'body' => $article['body'] ?? [],
                'gallery' => [],
                'is_published' => true,
                'published_at' => Carbon::parse($article['publishedAt']),
            ]);
        }
    }

    protected function seedEvents(): void
    {
        foreach ($this->data('events') as $event) {
            Event::updateOrCreate(['slug' => $event['slug']], [
                'title' => $event['title'],
                'category' => $event['category'],
                'icon' => $event['icon'],
                'accent' => $event['accent'],
                'excerpt' => $event['excerpt'] ?? null,
                'location' => $event['location'] ?? null,
                'organizer' => $event['organizer'] ?? null,
                'audience' => $event['audience'] ?? null,
                'registration_url' => ($event['registration_url'] ?? '') ?: null,
                'registration_label' => ($event['registration_label'] ?? '') ?: null,
                'image' => $event['image'] ?? null,
                'image_caption' => $event['image_caption'] ?? null,
                'tags' => $event['tags'] ?? [],
                'body' => $event['body'] ?? [],
                'rundown' => $event['rundown'] ?? [],
                'gallery' => [],
                'is_published' => true,
                // Waktu relatif ("+10 days 08:00") diubah ke tanggal absolut
                // agar hitung mundur selalu menuju masa depan setelah seeding.
                'starts_at' => Carbon::parse($event['startsAt']),
                'ends_at' => isset($event['endsAt']) ? Carbon::parse($event['endsAt']) : null,
            ]);
        }
    }

    /** Memuat berkas data awal di database/seeders/data. */
    protected function data(string $name): array
    {
        return require database_path("seeders/data/{$name}.php");
    }
}
