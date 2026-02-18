<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventPaginationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_events_index_returns_paginated_response(): void
    {
        Sanctum::actingAs($this->user);

        Event::query()->delete();
        Event::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/events?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonCount(10, 'data');
    }

    public function test_events_pagination_second_page(): void
    {
        Sanctum::actingAs($this->user);

        Event::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/events?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonCount(10, 'data');
    }

    public function test_events_pagination_default_values(): void
    {
        Sanctum::actingAs($this->user);

        Event::query()->delete();
        Event::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonCount(5, 'data');
    }

    public function test_events_pagination_respects_max_per_page_limit(): void
    {
        Sanctum::actingAs($this->user);

        Event::factory()->count(150)->create();

        $response = $this->getJson('/api/v1/events?per_page=200&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonCount(100, 'data');
    }

    public function test_events_pagination_min_per_page_limit(): void
    {
        Sanctum::actingAs($this->user);

        Event::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/events?per_page=0&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_events_pagination_empty_page(): void
    {
        Sanctum::actingAs($this->user);

        Event::query()->delete();
        Event::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/events?per_page=10&page=999');

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 999)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonCount(0, 'data');
    }

    public function test_events_pagination_includes_correct_links(): void
    {
        Sanctum::actingAs($this->user);

        Event::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/events?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('links.first', 'http://localhost/api/v1/events?page=1')
            ->assertJsonPath('links.last', 'http://localhost/api/v1/events?page=3')
            ->assertJsonPath('links.prev', null)
            ->assertJsonPath('links.next', 'http://localhost/api/v1/events?page=2');
    }
}
