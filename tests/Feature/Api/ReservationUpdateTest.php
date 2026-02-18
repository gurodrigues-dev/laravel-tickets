<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationUpdateTest extends TestCase
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

    public function test_user_can_reduce_reservation_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 95,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 2,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $reservation->id,
                'quantity' => 2,
                'status' => 'active',
            ]);

        $this->event->refresh();
        $this->assertEquals(98, $this->event->available_tickets);

        $reservation->refresh();
        $this->assertEquals(2, $reservation->quantity);
    }

    public function test_user_cannot_update_reservation_to_zero_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 95,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 0,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_user_cannot_update_reservation_to_negative_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 95,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => -1,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_user_cannot_update_another_users_reservation(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $otherUser->id,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 2,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You are not authorized to update this reservation',
            ]);
    }

    public function test_user_can_increase_reservation_quantity_when_tickets_available(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 2,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 98,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 5,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $reservation->id,
                'quantity' => 5,
                'status' => 'active',
            ]);

        $this->event->refresh();
        $this->assertEquals(95, $this->event->available_tickets);
    }

    public function test_user_cannot_increase_reservation_quantity_when_not_enough_tickets(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 2,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 2,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 5,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Not enough tickets available',
            ]);
    }

    public function test_version_conflict_prevents_update(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 95,
            'version' => 5,
        ]);

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 3,
            'version' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Version conflict. The event may have been modified by another user. Please refresh and try again.',
            ]);
    }

    public function test_validation_requires_quantity_field(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_validation_requires_version_field(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version']);
    }

    public function test_user_cannot_update_reservation_to_exceed_max_tickets_per_user(): void
    {
        Sanctum::actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 3,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 97,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$reservation->id, [
            'quantity' => 6,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'You can only reserve a maximum of 5 tickets per event',
            ]);
    }

    public function test_user_cannot_update_reservation_when_total_tickets_exceeds_max_per_user(): void
    {
        Sanctum::actingAs($this->user);

        $firstReservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 3,
            'status' => 'active',
        ]);

        $secondReservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 2,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 95,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$secondReservation->id, [
            'quantity' => 3,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'You already have 3 ticket(s) for this event. You can only reserve a maximum of 5 tickets per event.',
            ]);
    }

    public function test_user_can_update_reservation_within_max_tickets_per_user_limit(): void
    {
        Sanctum::actingAs($this->user);

        $firstReservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 2,
            'status' => 'active',
        ]);

        $secondReservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'quantity' => 2,
            'status' => 'active',
        ]);

        $this->event->update([
            'available_tickets' => 96,
        ]);

        $this->event->refresh();

        $response = $this->putJson('/api/reservations/'.$secondReservation->id, [
            'quantity' => 3,
            'version' => $this->event->version,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $secondReservation->id,
                'quantity' => 3,
                'status' => 'active',
            ]);

        $this->event->refresh();
        $this->assertEquals(95, $this->event->available_tickets);
    }
}
