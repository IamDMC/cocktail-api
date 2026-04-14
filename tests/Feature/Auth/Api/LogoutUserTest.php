<?php

namespace Tests\Feature\Auth\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('auth')]
    public function it_logs_out_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertOk()->assertJsonStructure([
            'message'
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    #[Test, Group('auth')]
    public function it_is_protected_from_unauthorized_access(): void
    {
        User::factory()->create();

        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();
    }
}
