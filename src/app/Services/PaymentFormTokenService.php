<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PaymentFormTokenService
{
    private const TTL_MINUTES = 120;

    public function issue(string $scope, ?int $userId = null): string
    {
        $token = (string) Str::uuid();
        $userId = $userId ?? auth()->id();

        Cache::put($this->pendingKey($scope, $token), $userId, now()->addMinutes(self::TTL_MINUTES));

        $tokens = session($scope, []);
        $tokens[] = $token;
        session([$scope => array_values(array_slice(array_unique($tokens), -20))]);

        return $token;
    }

    public function consume(string $scope, string $token, ?int $userId = null): bool
    {
        if ($token === '') {
            return false;
        }

        $userId = $userId ?? auth()->id();
        $pendingKey = $this->pendingKey($scope, $token);
        $owner = Cache::pull($pendingKey);
        $fromSession = $this->consumeFromSession($scope, $token);

        if ($owner === null && ! $fromSession) {
            return false;
        }

        if ($owner !== null && $userId !== null && (int) $owner !== (int) $userId) {
            // Put back so the rightful owner can still use it.
            Cache::put($pendingKey, $owner, now()->addMinutes(self::TTL_MINUTES));

            return false;
        }

        // Keep session in sync when token was only present in cache.
        if (! $fromSession) {
            $this->consumeFromSession($scope, $token);
        }

        return true;
    }

    public function restore(string $scope, string $token, ?int $userId = null): void
    {
        if ($token === '') {
            return;
        }

        $userId = $userId ?? auth()->id();
        Cache::put($this->pendingKey($scope, $token), $userId, now()->addMinutes(self::TTL_MINUTES));

        $tokens = session($scope, []);
        $tokens[] = $token;
        session([$scope => array_values(array_slice(array_unique($tokens), -20))]);
    }

    public function rememberSuccess(string $scope, string $token, array $payload): void
    {
        if ($token === '') {
            return;
        }

        Cache::put($this->doneKey($scope, $token), $payload, now()->addMinutes(self::TTL_MINUTES));
    }

    public function findSuccess(string $scope, string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $payload = Cache::get($this->doneKey($scope, $token));

        return is_array($payload) ? $payload : null;
    }

    private function consumeFromSession(string $scope, string $token): bool
    {
        $tokens = session($scope, []);
        if (! is_array($tokens)) {
            return false;
        }

        $index = array_search($token, $tokens, true);
        if ($index === false) {
            return false;
        }

        unset($tokens[$index]);
        session([$scope => array_values($tokens)]);

        return true;
    }

    private function pendingKey(string $scope, string $token): string
    {
        return "payment_form_token:pending:{$scope}:{$token}";
    }

    private function doneKey(string $scope, string $token): string
    {
        return "payment_form_token:done:{$scope}:{$token}";
    }
}
