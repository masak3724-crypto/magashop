<?php

namespace App\Support;

class PhoneCountries
{
    /** @var list<array{code: string, dial: string, flag: string, name: string, groups: list<int>, nationalLength: int}> */
    private const COUNTRIES = [
        ['code' => 'RU', 'dial' => '7', 'flag' => '🇷🇺', 'name' => 'Россия', 'groups' => [3, 3, 2, 2], 'nationalLength' => 10],
        ['code' => 'KZ', 'dial' => '7', 'flag' => '🇰🇿', 'name' => 'Казахстан', 'groups' => [3, 3, 2, 2], 'nationalLength' => 10],
        ['code' => 'BY', 'dial' => '375', 'flag' => '🇧🇾', 'name' => 'Беларусь', 'groups' => [2, 3, 2, 2], 'nationalLength' => 9],
        ['code' => 'UA', 'dial' => '380', 'flag' => '🇺🇦', 'name' => 'Украина', 'groups' => [2, 3, 2, 2], 'nationalLength' => 9],
        ['code' => 'UZ', 'dial' => '998', 'flag' => '🇺🇿', 'name' => 'Узбекистан', 'groups' => [2, 3, 2, 2], 'nationalLength' => 9],
        ['code' => 'AM', 'dial' => '374', 'flag' => '🇦🇲', 'name' => 'Армения', 'groups' => [2, 3, 2, 2], 'nationalLength' => 8],
        ['code' => 'GE', 'dial' => '995', 'flag' => '🇬🇪', 'name' => 'Грузия', 'groups' => [3, 3, 2, 2], 'nationalLength' => 9],
        ['code' => 'DE', 'dial' => '49', 'flag' => '🇩🇪', 'name' => 'Германия', 'groups' => [3, 3, 4], 'nationalLength' => 10],
        ['code' => 'US', 'dial' => '1', 'flag' => '🇺🇸', 'name' => 'США', 'groups' => [3, 3, 4], 'nationalLength' => 10],
        ['code' => 'GB', 'dial' => '44', 'flag' => '🇬🇧', 'name' => 'Великобритания', 'groups' => [4, 3, 3], 'nationalLength' => 10],
        ['code' => 'CN', 'dial' => '86', 'flag' => '🇨🇳', 'name' => 'Китай', 'groups' => [3, 4, 4], 'nationalLength' => 11],
    ];

    /** @return list<array{code: string, dial: string, flag: string, name: string, groups: list<int>, nationalLength: int}> */
    public static function all(): array
    {
        return self::COUNTRIES;
    }

    public static function findByCode(string $code): ?array
    {
        foreach (self::COUNTRIES as $country) {
            if ($country['code'] === $code) {
                return $country;
            }
        }

        return null;
    }

    public static function isValid(?string $phone): bool
    {
        if ($phone === null || $phone === '') {
            return true;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        foreach (self::COUNTRIES as $country) {
            $dial = $country['dial'];
            if (! str_starts_with($digits, $dial)) {
                continue;
            }

            $national = substr($digits, strlen($dial));

            if (strlen($national) === $country['nationalLength']) {
                return self::format($country, $national) === self::normalizeSpaces($phone);
            }
        }

        return false;
    }

    /** @return array{country: array, national: string}|null */
    public static function parse(?string $phone): ?array
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        $sorted = self::COUNTRIES;
        usort($sorted, fn ($a, $b) => strlen($b['dial']) <=> strlen($a['dial']));

        foreach ($sorted as $country) {
            $dial = $country['dial'];
            if (! str_starts_with($digits, $dial)) {
                continue;
            }

            $national = substr($digits, strlen($dial));

            if (strlen($national) === $country['nationalLength']) {
                return ['country' => $country, 'national' => $national];
            }
        }

        return null;
    }

    /**
     * @param  array{groups: list<int>, dial: string}  $country
     */
    public static function format(array $country, string $nationalDigits): string
    {
        $parts = [];
        $offset = 0;

        foreach ($country['groups'] as $size) {
            $parts[] = substr($nationalDigits, $offset, $size);
            $offset += $size;
        }

        return '+'.$country['dial'].' '.implode(' ', array_filter($parts));
    }

    private static function normalizeSpaces(string $phone): string
    {
        return preg_replace('/\s+/', ' ', trim($phone)) ?? '';
    }
}
