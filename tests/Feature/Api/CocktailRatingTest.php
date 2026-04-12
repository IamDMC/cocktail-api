<?php

namespace Api;

use App\Models\Cocktail;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CocktailRatingTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_creates_cocktail_rating(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 1,
            'comment' => 'test-abc-123'
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertCreated();

        $this->assertDatabaseCount('ratings', 1);

        $this->assertDatabaseHas('ratings', [
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'user_id' => $user->id,
            'cocktail_id' => $cocktail->id
        ]);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_creates_cocktail_rating_without_comment(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 5,
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);
        $response->assertCreated();

        $this->assertDatabaseCount('ratings', 1);

        $this->assertDatabaseHas('ratings', [
            'rating' => $data['rating'],
            'comment' => null,
            'user_id' => $user->id,
            'cocktail_id' => $cocktail->id
        ]);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_updates_cocktail_rating_without_comment(): void
    {
        $user = User::factory()->create();

        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        Rating::create([
            'rating' => 5,
            'comment' => 'test-abc-123',
            'user_id' => $user->id,
            'cocktail_id' => $cocktail->id
        ]);

        $data = [
            'rating' => 1,
        ];

        $response = $this->putJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertOk();

        $this->assertDatabaseCount('ratings', 1);

        $this->assertDatabaseHas('ratings', [
            'rating' => $data['rating'],
            'comment' => null,
            'user_id' => $user->id,
            'cocktail_id' => $cocktail->id
        ]);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_updates_cocktail_rating(): void
    {
        $user = User::factory()->create();

        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        Rating::create([
            'rating' => 5,
            'comment' => 'test-abc-123',
            'user_id' => $user->id,
            'cocktail_id' => $cocktail->id
        ]);

        $data = [
            'rating' => 1,
            'comment' => 'test-def-456'
        ];

        $response = $this->putJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertOk();

        $this->assertDatabaseCount('ratings', 1);

        $this->assertDatabaseHas('ratings', [
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'user_id' => $user->id,
            'cocktail_id' => $cocktail->id
        ]);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_validates_cocktail_rating_required(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'comment' => 'test-abc-123'
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertJsonValidationErrors(['rating']);

        $this->assertDatabaseCount('ratings', 0);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_validates_cocktail_rating_to_be_integer(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 'abc',
            'comment' => 'test-abc-123'
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertJsonValidationErrors(['rating']);

        $this->assertDatabaseCount('ratings', 0);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_validates_cocktail_min_rating(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 0,
            'comment' => 'test-abc-123'
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertJsonValidationErrors(['rating']);

        $this->assertDatabaseCount('ratings', 0);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_validates_cocktail_max_rating(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 6,
            'comment' => 'test-abc-123'
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertJsonValidationErrors(['rating']);

        $this->assertDatabaseCount('ratings', 0);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_validates_cocktail_comment_to_be_string(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 5,
            'comment' => 5,
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertJsonValidationErrors(['comment']);

        $this->assertDatabaseCount('ratings', 0);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_validates_cocktail_comment_min_length(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 5,
            'comment' => 'abc',
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertJsonValidationErrors(['comment']);

        $this->assertDatabaseCount('ratings', 0);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_validates_cocktail_comment_max_length(): void
    {
        $user = User::factory()->create();
        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $data = [
            'rating' => 5,
            'comment' => str_repeat('a', 256),
        ];

        $response = $this->postJson("/api/rating/cocktails/{$cocktail->id}", $data);

        $response->assertJsonValidationErrors(['comment']);

        $this->assertDatabaseCount('ratings', 0);
    }
}
