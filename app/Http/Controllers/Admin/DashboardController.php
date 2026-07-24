<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Activity;
use App\Models\Event;
use App\Models\Media;
use App\Models\News;
use App\Models\Pillar;
use Inertia\Inertia;
use Inertia\Response;

/** Ringkasan isi situs + pintasan ke tugas yang paling sering dipakai. */
class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                ['label' => 'Berita Terbit', 'value' => News::published()->count(), 'icon' => '📰', 'href' => route('admin.news.index')],
                ['label' => 'Draf Berita', 'value' => News::where('is_published', false)->count(), 'icon' => '📝', 'href' => route('admin.news.index')],
                ['label' => 'Agenda Mendatang', 'value' => Event::published()->upcoming()->count(), 'icon' => '📅', 'href' => route('admin.events.index')],
                ['label' => 'Prestasi', 'value' => Achievement::count(), 'icon' => '🏆', 'href' => route('admin.resources.index', 'achievements')],
                ['label' => 'Kegiatan', 'value' => Activity::count(), 'icon' => '🎯', 'href' => route('admin.resources.index', 'activities')],
                ['label' => 'Pilar Keunggulan', 'value' => Pillar::count(), 'icon' => '🏛️', 'href' => route('admin.resources.index', 'pillars')],
                ['label' => 'Gambar Tersimpan', 'value' => Media::count(), 'icon' => '🖼️', 'href' => route('admin.media.index')],
            ],
            'recentNews' => News::latestFirst()->limit(5)->get()->map(fn (News $news) => [
                'id' => $news->id,
                'title' => $news->title,
                'category' => $news->category,
                'date' => $news->formattedDate(),
                'is_published' => $news->is_published,
                'edit_url' => route('admin.news.edit', $news->id),
            ]),
        ]);
    }
}
