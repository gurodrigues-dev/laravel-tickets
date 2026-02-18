<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationCreateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $user;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->event = Event::factory()->create([
            'total_tickets' => 100,
            'available_tickets' => 100,
            'version' => 1,
            'max_tickets_per_user' => 5,
        ]);
    }

    public function test_user_can_create_reservation_within_limits(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => $this->event->id,
            'quantity' => 3,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'event_id',
                'user_id',
                'quantity',
                'status',
                'event',
            ]);

        $reservation = Reservation::where('user_id', $this->user->id)
            ->where('event_id', $this->event->id)
            ->first();

        $this->assertNotNull($reservation);
        $this->assertEquals(3, $reservation->quantity);
        $this->assertEquals('active', $reservation->status);

        $this->event->refresh();
        $this->assertEquals(97, $this->event->available_tickets);
    }

    public function test_user_cannot_create_reservation_exceeding_max_tickets_per_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => $this->event->id,
            'quantity' => 6,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'You can only reserve a maximum of 5 tickets per event',
            ]);

        $reservation = Reservation::where('user_id', $this->user->id)
            ->where('event_id', $this->event->id)
            ->first();

        $this->assertNull($reservation);
    }

    public function test_user_cannot_create_reservation_when_not_enough_tickets_available(): void
    {
        Sanctum::actingAs($this->user);

        $this->event->update([
            'available_tickets' => 2,
        ]);

        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => $this->event->id,
            'quantity' => 5,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Not enough tickets available',
            ]);

        $reservation = Reservation::where('user_id', $this->user->id)
            ->where('event_id', $this->event->id)
            ->first();

        $this->assertNull($reservation);
    }

    public function test_user_cannot_create_reservation_with_zero_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => $this->event->id,
            'quantity' => 0,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_create_reservation_with_negative_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => $this->event->id,
            'quantity' => -1,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_create_reservation_for_nonexistent_event(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => 9999,
            'quantity' => 3,
            'version' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
            ]);
    }

    public function test_user_must_be_authenticated_to_create_reservation(): void
    {
        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => $this->event->id,
            'quantity' => 3,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(401);
    }

    public function test_version_conflict_prevents_creation(): void
    {
        Sanctum::actingAs($this->user);

        $this->event->update([
            'version' => 5,
        ]);

        $response = $this->postJson('/api/v1/reservations', [
            'event_id' => $this->event->id,
            'quantity' => 3,
            'version' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Version conflict. The event may have been modified by another user. Please refresh and try again.',
            ]);
    }
}
