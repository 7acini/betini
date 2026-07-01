<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBusinessProfileReviewService
{
    public function reviews(): array
    {
        if (! $this->isConfigured()) {
            return $this->emptyPayload(configured: false);
        }

        return Cache::remember('google_business_profile.reviews', now()->addMinutes(30), function (): array {
            try {
                $accessToken = $this->accessToken();

                if ($accessToken === null) {
                    return $this->emptyPayload(configured: true);
                }

                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withToken($accessToken)
                    ->get($this->reviewsUrl(), [
                        'pageSize' => $this->reviewsLimit(),
                        'orderBy' => 'updateTime desc',
                    ]);

                if (! $response->ok()) {
                    Log::warning('google_business_profile.reviews_failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return $this->emptyPayload(configured: true);
                }

                $payload = $response->json() ?? [];

                return [
                    'configured' => true,
                    'synced' => true,
                    'averageRating' => $payload['averageRating'] ?? null,
                    'totalReviewCount' => $payload['totalReviewCount'] ?? null,
                    'reviews' => collect($payload['reviews'] ?? [])
                        ->map(fn (array $review): array => $this->normalizeReview($review))
                        ->filter(fn (array $review): bool => $review['comment'] !== '')
                        ->values()
                        ->all(),
                ];
            } catch (\Throwable $exception) {
                Log::warning('google_business_profile.reviews_exception', [
                    'exception' => $exception->getMessage(),
                ]);

                return $this->emptyPayload(configured: true);
            }
        });
    }

    private function accessToken(): ?string
    {
        return Cache::remember('google_business_profile.access_token', now()->addMinutes(50), function (): ?string {
            $response = Http::asForm()
                ->timeout(10)
                ->post((string) config('services.google_business_profile.token_url'), [
                    'client_id' => config('services.google_business_profile.client_id'),
                    'client_secret' => config('services.google_business_profile.client_secret'),
                    'refresh_token' => config('services.google_business_profile.refresh_token'),
                    'grant_type' => 'refresh_token',
                ]);

            if (! $response->ok()) {
                Log::warning('google_business_profile.token_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    private function isConfigured(): bool
    {
        return collect([
            config('services.google_business_profile.client_id'),
            config('services.google_business_profile.client_secret'),
            config('services.google_business_profile.refresh_token'),
            config('services.google_business_profile.account_id'),
            config('services.google_business_profile.location_id'),
        ])->every(fn ($value): bool => filled($value));
    }

    private function reviewsUrl(): string
    {
        $baseUrl = rtrim((string) config('services.google_business_profile.base_url'), '/');
        $accountId = trim((string) config('services.google_business_profile.account_id'), '/');
        $locationId = trim((string) config('services.google_business_profile.location_id'), '/');

        return "{$baseUrl}/accounts/{$accountId}/locations/{$locationId}/reviews";
    }

    private function reviewsLimit(): int
    {
        return min(max((int) config('services.google_business_profile.reviews_limit', 10), 1), 50);
    }

    private function normalizeReview(array $review): array
    {
        return [
            'id' => $review['reviewId'] ?? $review['name'] ?? null,
            'author' => $review['reviewer']['displayName'] ?? 'Cliente Google',
            'rating' => $this->ratingValue((string) ($review['starRating'] ?? '')),
            'comment' => trim((string) ($review['comment'] ?? '')),
            'createTime' => $review['createTime'] ?? null,
            'updateTime' => $review['updateTime'] ?? null,
        ];
    }

    private function ratingValue(string $rating): int
    {
        return match ($rating) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
            default => 0,
        };
    }

    private function emptyPayload(bool $configured): array
    {
        return [
            'configured' => $configured,
            'synced' => false,
            'averageRating' => null,
            'totalReviewCount' => null,
            'reviews' => [],
        ];
    }
}
