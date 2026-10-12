<?php

namespace App\Services;

use App\Models\AccountMapping;
use App\Models\Coa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AccountMap
{
    public static function get(string $mappingKey): ?Coa
    {
        $cacheKey = self::cacheKey($mappingKey);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $account = AccountMapping::where('mapping_key', $mappingKey)
            ->with('account')
            ->first()
            ?->account;

        if ($account) {
            Cache::forever($cacheKey, $account);
        }

        return $account;
    }

    public static function id(string $mappingKey): ?int
    {
        return optional(self::get($mappingKey))->id;
    }

    public static function all(): Collection
    {
        return Cache::rememberForever(self::cacheKey('all'), function () {
            return AccountMapping::with('account')
                ->get()
                ->filter(fn(AccountMapping $mapping) => $mapping->account !== null)
                ->mapWithKeys(fn(AccountMapping $mapping) => [$mapping->mapping_key => $mapping->account]);
        });
    }

    public static function refresh(string $mappingKey): void
    {
        Cache::forget(self::cacheKey($mappingKey));
        Cache::forget(self::cacheKey('all'));
    }

    public static function refreshAll(): void
    {
        foreach (array_keys(self::mappingLabels()) as $mappingKey) {
            self::refresh($mappingKey);
        }
        Cache::forget(self::cacheKey('all'));
    }

    public static function mappingLabels(): array
    {
        return AccountMapping::mappingKeys();
    }

    protected static function cacheKey(string $mappingKey): string
    {
        return "account_map:{$mappingKey}";
    }
}
