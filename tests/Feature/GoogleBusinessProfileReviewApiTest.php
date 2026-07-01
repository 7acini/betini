<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBusinessProfileReviewApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_google_reviews_fallback_when_integration_is_not_configured(): void
    {
        $this->getJson('/api/landing/google-reviews')
            ->assertOk()
            ->assertJsonPath('configured', false)
            ->assertJsonPath('synced', false)
            ->assertJsonPath('reviews', []);
    }

    public function test_google_reviews_can_be_synced_from_business_profile_api(): void
    {
        Config::set('services.google_business_profile.client_id', 'client-id');
        Config::set('services.google_business_profile.client_secret', 'client-secret');
        Config::set('services.google_business_profile.refresh_token', 'refresh-token');
        Config::set('services.google_business_profile.account_id', '123');
        Config::set('services.google_business_profile.location_id', '456');
        Config::set('services.google_business_profile.reviews_limit', 10);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-token',
                'expires_in' => 3600,
            ]),
            'mybusiness.googleapis.com/v4/accounts/123/locations/456/reviews*' => Http::response([
                'averageRating' => 4.8,
                'totalReviewCount' => 25,
                'reviews' => [
                    [
                        'reviewId' => 'review-1',
                        'reviewer' => ['displayName' => 'Cliente Real'],
                        'starRating' => 'FIVE',
                        'comment' => 'Atendimento excelente e muito transparente.',
                        'createTime' => '2026-06-30T12:00:00Z',
                        'updateTime' => '2026-06-30T12:00:00Z',
                    ],
                ],
            ]),
        ]);

        $this->getJson('/api/landing/google-reviews')
            ->assertOk()
            ->assertJsonPath('configured', true)
            ->assertJsonPath('synced', true)
            ->assertJsonPath('averageRating', 4.8)
            ->assertJsonPath('totalReviewCount', 25)
            ->assertJsonPath('reviews.0.author', 'Cliente Real')
            ->assertJsonPath('reviews.0.rating', 5)
            ->assertJsonPath('reviews.0.comment', 'Atendimento excelente e muito transparente.');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'orderBy=updateTime%20desc'));
    }
}
