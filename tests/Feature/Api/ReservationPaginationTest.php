<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationPaginationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->event = Event::factory()->create([
            'total_tickets' => 1000,
            'available_tickets' => 1000,
            'version' => 1,
            'max_tickets_per_user' => 10,
        ]);
    }

    public function test_user_reservations_returns_paginated_response(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=10&page=1');

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
            ->assertJsonPath('meta.total', 15)
            ->assertJsonCount(10, 'data');
    }

    public function test_user_reservations_pagination_second_page(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_reservations_pagination_default_values(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations');

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_reservations_pagination_respects_max_per_page_limit(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(150)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=200&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonCount(100, 'data');
    }

    public function test_user_reservations_pagination_min_per_page_limit(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=0&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_reservations_pagination_empty_page(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=10&page=999');

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 999)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonCount(0, 'data');
    }

    public function test_user_reservations_pagination_includes_event_relationship(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'event_id',
                        'user_id',
                        'quantity',
                        'status',
                        'event',
                    ],
                ],
            ]);

        $this->assertNotNull($response->json('data.0.event'));
    }

    public function test_user_reservations_pagination_only_returns_authenticated_user_reservations(): void
    {
        $otherUser = User::factory()->create();

        Reservation::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        Reservation::factory()->count(5)->create([
            'user_id' => $otherUser->id,
            'event_id' => $this->event->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=20&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 10);

        foreach ($response->json('data') as $reservation) {
            $this->assertEquals($this->user->id, $reservation['user_id']);
        }
    }

    public function test_user_reservations_pagination_includes_correct_links(): void
    {
        Sanctum::actingAs($this->user);

        Reservation::factory()->count(25)->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->getJson('/api/v1/reservations/my-reservations?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('links.first', 'http://localhost/api/v1/reservations/my-reservations?page=1')
            ->assertJsonPath('links.last', 'http://localhost/api/v1/reservations/my-reservations?page=3')
            ->assertJsonPath('links.prev', null)
            ->assertJsonPath('links.next', 'http://localhost/api/v1/reservations/my-reservations?page=2');
    }
}
