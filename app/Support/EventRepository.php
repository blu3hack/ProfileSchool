<?php

namespace App\Support;

use App\Models\Event;

/**
 * Sumber tunggal seluruh data agenda ("Next Event").
 *
 * Kontrak datanya sengaja dibuat mirip NewsRepository supaya komponen Vue
 * yang menampilkan kartu bisa memakai pola yang sama, hanya dengan tambahan
 * field waktu (`startsAt`, `endsAt`) untuk hitung mundur.
 */
class EventRepository
{
    /** Kategori untuk filter di halaman indeks, lengkap dengan jumlahnya. */
    public static function categories(): array
    {
        return Event::published()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => $row->category, 'count' => (int) $row->total])
            ->all();
    }

    /** N acara terdekat — dipakai section "Next Event" di landing page. */
    public static function upcoming(int $limit = 4): array
    {
        return Event::published()->upcoming()->soonest()->limit($limit)->get()
            ->map(fn (Event $event) => $event->toCard())
            ->all();
    }

    /** Seluruh acara mendatang, yang paling dekat lebih dulu. */
    public static function allUpcoming(): array
    {
        return Event::published()->upcoming()->soonest()->get()
            ->map(fn (Event $event) => $event->toCard())
            ->all();
    }

    /** Arsip acara yang sudah terlaksana, terbaru lebih dulu. */
    public static function past(int $limit = 6): array
    {
        return Event::published()->past()->latestFirst()->limit($limit)->get()
            ->map(fn (Event $event) => $event->toCard())
            ->all();
    }

    /** Satu acara berdasarkan slug, atau null bila tidak ada. */
    public static function find(string $slug): ?array
    {
        $event = Event::published()->where('slug', $slug)->first();

        return $event?->toArticle();
    }

    /**
     * Agenda lain yang bisa dilihat setelah membaca satu acara: acara
     * mendatang terdekat, dan bila belum cukup dilengkapi arsip terbaru.
     */
    public static function related(string $slug, int $limit = 3): array
    {
        $upcoming = Event::published()->upcoming()->soonest()
            ->where('slug', '!=', $slug)
            ->limit($limit)
            ->get();

        if ($upcoming->count() < $limit) {
            $past = Event::published()->past()->latestFirst()
                ->where('slug', '!=', $slug)
                ->limit($limit - $upcoming->count())
                ->get();

            $upcoming = $upcoming->concat($past);
        }

        return $upcoming->map(fn (Event $event) => $event->toCard())->values()->all();
    }
}
