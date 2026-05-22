<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class Wildberries
{
    public static function basketHost(int $vol): string
    {
        $map = [
            [143, '01'], [287, '02'], [431, '03'], [719, '04'], [1007, '05'], [1061, '06'],
            [1115, '07'], [1169, '08'], [1313, '09'], [1601, '10'], [1655, '11'], [1919, '12'],
            [2045, '13'], [2189, '14'], [2405, '15'], [2621, '16'], [2837, '17'], [3053, '18'],
        ];

        foreach ($map as [$max, $host]) {
            if ($vol <= $max) {
                return $host;
            }
        }

        return '18';
    }

    public static function imageUrl(int $nmId, int $photo = 1): string
    {
        $vol = (int) floor($nmId / 100000);
        $part = (int) floor($nmId / 1000);
        $host = self::basketHost($vol);

        return "https://basket-{$host}.wbbasket.ru/vol{$vol}/part{$part}/{$nmId}/images/big/{$photo}.webp";
    }

    public static function cardJsonUrl(int $nmId): string
    {
        $vol = (int) floor($nmId / 100000);
        $part = (int) floor($nmId / 1000);
        $host = self::basketHost($vol);

        return "https://basket-{$host}.wbbasket.ru/vol{$vol}/part{$part}/{$nmId}/info/ru/card.json";
    }

    /** @return array<string, mixed>|null */
    public static function fetchCard(int $nmId): ?array
    {
        try {
            $response = Http::timeout(25)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0',
                    'Accept' => 'application/json',
                ])
                ->get(self::cardJsonUrl($nmId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            return is_array($data) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function guessCategory(array $card, ?string $fallback = 'odezhda'): string
    {
        $subject = mb_strtolower((string) ($card['subj_name'] ?? ''));
        $root = mb_strtolower((string) ($card['subj_root_name'] ?? ''));
        $name = mb_strtolower((string) ($card['imt_name'] ?? ''));
        $hay = $subject.' '.$root.' '.$name;

        if (preg_match('/обув|кроссов|ботин|туфл|лофер|сапог|босонож|кед|сандал|шлепан|мокасин|сникер/u', $hay)) {
            return 'obuv';
        }

        if (preg_match('/сумк|рюкзак|ремен|шарф|очк|часы|кошел|браслет|серьг|колье|перчат|шапк|платок|аксессуар|бижутер/u', $hay)) {
            return 'aksessuary';
        }

        $gender = mb_strtolower((string) ($card['options'][0]['value'] ?? ''));
        foreach ($card['grouped_options'] ?? [] as $group) {
            foreach ($group['options'] ?? [] as $opt) {
                if (mb_strtolower((string) ($opt['name'] ?? '')) === 'пол') {
                    $gender = mb_strtolower((string) ($opt['value'] ?? $gender));
                }
            }
        }

        if (str_contains($gender, 'муж')) {
            return 'muzhchinam';
        }

        return $fallback ?? 'odezhda';
    }

    public static function productName(array $card): string
    {
        $name = trim((string) ($card['imt_name'] ?? ''));
        if ($name !== '') {
            return mb_strtoupper(mb_substr($name, 0, 1)).mb_substr($name, 1);
        }

        return 'Товар '.($card['nm_id'] ?? '');
    }

    public static function genericDescription(string $name, string $categorySlug): string
    {
        $tail = match ($categorySlug) {
            'obuv' => 'Удобная посадка и актуальный дизайн для города и отдыха.',
            'aksessuary' => 'Завершит образ и подойдёт для повседневных сочетаний.',
            'muzhchinam' => 'Современный крой и комфорт на каждый день.',
            default => 'Качественные материалы и универсальный силуэт.',
        };

        return $name.'. '.$tail;
    }
}
