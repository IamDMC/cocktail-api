<?php

namespace Tests\Feature\Auth\Api;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('auth')]
    public function it_creates_user(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password123!',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(201);

        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
            'name' => $data['name'],
        ]);

        $user = User::query()->first();

        $this->assertTrue(
            Hash::check($data['password'], $user->password)
        );
    }

    #[Test, Group('auth')]
    public function it_dispatches_registered_event_on_register(): void
    {
        Event::fake();

        $data = [
            'email' => 'test@test.at',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $this->postJson('/api/auth/register', $data)
            ->assertCreated();

        Event::assertDispatched(Registered::class);
    }

    #[Test, Group('auth')]
    public function it_sends_email_verification_notification(): void
    {
        Notification::fake();

        $data = [
            'email' => 'test@test.at',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $this->postJson('/api/auth/register', $data)
            ->assertCreated();

        $user = User::first();

        Notification::assertSentTo(
            $user,
            \App\Notifications\VerifyEmailNotification::class
        );
    }

    #[Test, Group('auth')]
    public function it_resends_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $token = $user->createToken('api')->plainTextToken;

        $this->postJson('/api/email/verification-notification', [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk();

        Notification::assertSentTo(
            $user,
            \App\Notifications\VerifyEmailNotification::class
        );
    }

    #[Test, Group('auth')]
    public function it_validates_email_required(): void
    {

        $data = [
            'password' =>  'Password123!',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_email_format(): void
    {

        $data = [
            'email' => 'test.at',
            'password' =>  'Password123!',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_unique_users_email(): void
    {
        User::factory()->create(['email' => 'test@test.at']);

        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password123!',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 1);
    }

    #[Test, Group('auth')]
    public function it_validates_password_required(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_password_confirmation_matches_password(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password12!',
            'password_confirmation' => 'Password123!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_password_min_length(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Passw1!',
            'password_confirmation' => 'Passw2!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_password_contains_letters(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  '12345678',
            'password_confirmation' => '12345678',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_password_contains_mixed_case_letters(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'password123!',
            'password_confirmation' => 'password123!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_password_contains_numbers(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'PasswordABC!',
            'password_confirmation' => 'PasswordABC!',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_password_contains_symbols(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password123',
            'password_confirmation' => 'Password123',
            'name' => 'Maxi',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_name_required(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password123',
            'password_confirmation' => 'Password123',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_name_to_be_string(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password123',
            'password_confirmation' => 'Password123',
            'name' => 1
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_name_min_length(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password123',
            'password_confirmation' => 'Password123',
            'name' => 'abc'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('auth')]
    public function it_validates_name_max_length(): void
    {
        $data = [
            'email' => 'test@test.at',
            'password' =>  'Password123',
            'password_confirmation' => 'Password123',
            'name' => str_repeat('a', 61)
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }
}
