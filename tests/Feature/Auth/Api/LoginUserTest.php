<?php

namespace Tests\Feature\Auth\Api;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('auth')]
    public function it_logs_in_user(): void
    {
        $user = User::factory()->create([
            'password' => 'Password123!'
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertOk();

        $response->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'email',
                'name',
            ],
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    #[Test, Group('auth')]
    public function it_validates_email_required(): void
    {
        User::factory()->create([
            'password' => 'Password123!'
        ]);

        $credentials = [
            //'email' => $user->email,
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertJsonValidationErrors(['email']);
    }

    #[Test, Group('auth')]
    public function it_validates_email_format(): void
    {
        User::factory()->create([
            'email' => 'test@test.at',
            'password' => 'Password123!'
        ]);

        $credentials = [
            'email' => 'testtest.at',
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertJsonValidationErrors(['email']);
    }

    #[Test, Group('auth')]
    public function it_validates_password_required(): void
    {
        User::factory()->create([
            'email' => 'test@test.at',
            'password' => 'Password123!'
        ]);

        $credentials = [
            'email' => 'testtest.at',
            //'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertJsonValidationErrors(['password']);
    }

    #[Test, Group('auth')]
    public function it_checks_password(): void
    {
        User::factory()->create([
            'email' => 'test@test.at',
            'password' => 'Password123!'
        ]);

        $credentials = [
            'email' => 'test@test.at',
            'password' => 'Password123',
        ];

        $this->postJson('/api/auth/login', $credentials)
            ->assertStatus(403);
    }

    #[Test, Group('auth')]
    public function it_checks_if_email_is_verified(): void
    {
        User::factory()->unverified()->create([
            'email' => 'test@test.at',
            'password' => 'Password123!'
        ]);

        $credentials = [
            'email' => 'test@test.at',
            'password' => 'Password123!',
        ];

        $this->postJson('/api/auth/login', $credentials)
            ->assertStatus(403);
    }
}
