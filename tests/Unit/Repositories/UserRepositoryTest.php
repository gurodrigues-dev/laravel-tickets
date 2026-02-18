<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $repository = new UserRepository;
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ];

        $user = $repository->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_can_be_found_by_id(): void
    {
        $repository = new UserRepository;
        $user = User::factory()->create();

        $foundUser = $repository->find($user->id);

        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals($user->email, $foundUser->email);
    }

    public function test_user_can_be_found_by_email(): void
    {
        $repository = new UserRepository;
        $user = User::factory()->create(['email' => 'findme@example.com']);

        $foundUser = User::where('email', 'findme@example.com')->first();

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_user_can_update_profile(): void
    {
        $repository = new UserRepository;
        $user = User::factory()->create(['name' => 'Original Name']);
        $updateData = ['name' => 'Updated Name'];

        $updatedUser = $repository->update($user->id, $updateData);

        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals($user->email, $updatedUser->email);
    }

    public function test_user_can_update_email(): void
    {
        $repository = new UserRepository;
        $user = User::factory()->create(['email' => 'old@example.com']);
        $updateData = ['email' => 'new@example.com'];

        $updatedUser = $repository->update($user->id, $updateData);

        $this->assertEquals('new@example.com', $updatedUser->email);
    }

    public function test_user_can_delete_account(): void
    {
        $repository = new UserRepository;
        $user = User::factory()->create();

        $deleted = $repository->delete($user->id);

        $this->assertEquals(1, $deleted);
        $this->assertNull(User::find($user->id));
    }

    public function test_all_users_can_be_retrieved(): void
    {
        $repository = new UserRepository;
        User::factory()->count(5)->create();

        $users = $repository->all();

        $this->assertCount(5, $users);
        $this->assertInstanceOf(User::class, $users->first());
    }
}
