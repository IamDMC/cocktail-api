<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\Concerns\Has;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Cocktail\ImageTestHelper;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, ImageTestHelper;

    #[Test, Group('user-profile')]
    public function it_shows_user(): void
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonFragments([
               'data' => [
                   'id' => $user->id,
                   'email' => $user->email,
                   'name' => $user->name,
                   'email_verified_at' => $user->email_verified_at
               ]
            ]);
    }

    #[Test, Group('user-profile')]
    public function it_updates_user_email(): void
    {
        $user = User::factory()->create();

        $data = [
            'email' => 'test@test.at'
        ];

        Sanctum::actingAs($user);

        $this->putJson('/api/user', $data)
            ->assertOk()
            ->assertJsonFragments([
                'data' => [
                    'id' => $user->id,
                    'email' => $data['email'],
                    'name' => $user->name,
                    'email_verified_at' => $user->email_verified_at
                ]
            ]);
    }

    #[Test, Group('user-profile')]
    public function it_updates_user_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);

        $data = [
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!'
        ];

        Sanctum::actingAs($user);

        $this->putJson('/api/user', $data)
            ->assertOk()
            ->assertJsonFragments([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'email_verified_at' => $user->email_verified_at
                ]
            ]);

        $user = User::query()->first();

        $this->assertTrue(
            Hash::check($data['password'], $user->password)
        );
    }

    #[Test, Group('user-profile')]
    public function it_updates_user_name(): void
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);

        $data = [
            'name' => 'test-abc-123',
        ];

        Sanctum::actingAs($user);

        $this->putJson('/api/user', $data)
            ->assertOk()
            ->assertJsonFragments([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $data['name'],
                    'email_verified_at' => $user->email_verified_at
                ]
            ]);
    }

    #[Test, Group('user-profile')]
    public function it_deletes_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->deleteJson('/api/user')
            ->assertNoContent();

        $this->assertDatabaseCount('users', 0);
    }

    #[Test, Group('user-profile')]
    public function it_validates_email_format_on_update(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'email' => 'invalid-email'                  // Invalid data
        ])->assertJsonValidationErrors(['email']);
    }

    #[Test, Group('user-profile')]
    public function it_validates_email_to_be_unique_on_update(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'email' => $otherUser->email                    // Invalid data
        ])->assertJsonValidationErrors(['email']);
    }

    #[Test, Group('user-profile')]
    public function it_validates_password_confirmation_on_update(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'password' => 'Password1!',
            'password_confirmation' => 'wrong'                  // Invalid data
        ])->assertJsonValidationErrors(['password']);
    }

    #[Test, Group('user-profile')]
    public function it_validates_password_rules_on_update(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'password' => 'weak',                       // Invalid data
            'password_confirmation' => 'weak'
        ])->assertJsonValidationErrors(['password']);
    }

    #[Test, Group('user-profile')]
    public function it_validates_name_on_update(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'name' => 'a'                           // Invalid data
        ])->assertJsonValidationErrors(['name']);
    }

    #[Test, Group('user-profile')]
    public function it_updates_only_provided_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'old-name',
            'email' => 'old@test.at'
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'name' => 'new-name'
        ])->assertOk();

        $user->refresh();

        $this->assertEquals('new-name', $user->name);
        $this->assertEquals('old@test.at', $user->email); // wichtig
    }

    #[Test, Group('user-profile')]
    public function it_ignores_unique_email_for_same_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'email' => $user->email
        ])->assertOk();
    }

    #[Test, Group('user-profile')]
    public function it_requires_authentication(): void
    {
        $this->putJson('/api/user', [])
            ->assertUnauthorized();
    }

    #[Test, Group('user-profile')]
    public function it_resets_email_verification_when_email_changes(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'email' => 'new@test.at'
        ])->assertOk();

        $user->refresh();

        $this->assertNull($user->email_verified_at);
    }

    #[Test, Group('user-profile'), Group('image')]
    public function it_updates_user_with_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.png');

        $this->post(
            '/api/user',
            [
                '_method' => 'PUT',
                'image' => $file
            ],
            ['Accept' => 'application/json']
        )->assertOk();

        // Datei wurde gespeichert
        Storage::disk('public')->assertExists('images/'.$file->hashName());

        // DB Eintrag vorhanden
        $this->assertDatabaseHas('images', [
            'imageable_id' => $user->id,
            'imageable_type' => User::class,
        ]);
    }

    #[Test, Group('user-profile'), Group('image')]
    public function it_replaces_existing_user_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        // altes Bild
        $this->fakeHasImage($user, 'old.png', 'image/png');

        $new = UploadedFile::fake()->image('new.png');

        $this->post(
            '/api/user',
            [
                '_method' => 'PUT',
                'image' => $new
            ],
            ['Accept' => 'application/json']
        )->assertOk();

        Storage::disk('public')->assertExists('images/'.$new->hashName());
    }

    #[Test, Group('user-profile'), Group('image')]
    public function it_validates_user_image_type(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('file.pdf', 100, 'application/pdf');

        $this->post(
            '/api/user',
            [
                '_method' => 'PUT',
                'image' => $file
            ],
            ['Accept' => 'application/json']
        )->assertJsonValidationErrors(['image']);
    }
}
