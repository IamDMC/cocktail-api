<?php

namespace Tests\Feature\Cocktail\Actions;

use App\Actions\Cocktail\RateCocktailAction;
use App\Data\Rating\CreateRatingData;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Cocktail\CocktailTestCase;

class RateCocktailActionTest extends CocktailTestCase
{
    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_creates_cocktail_rating(): void
    {
        $cocktail = $this->createCocktail(
            $this->makeCocktail($this->user)
        );

        $ratingDto = new CreateRatingData(
            rating: 2,
            comment: 'test-abc-123',
            user_id: $this->user->id,
            cocktail_id: $cocktail->id
        );

        app(RateCocktailAction::class)->execute($ratingDto);

        $this->assertDatabaseCount('ratings', 1);

        $this->assertDatabaseHas('ratings', [
            'rating' => 2,
            'comment' => 'test-abc-123',
            'user_id' => $this->user->id,
            'cocktail_id' => $cocktail->id
        ]);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_updates_cocktail_rating(): void
    {
        $cocktail = $this->createCocktail(
            $this->makeCocktail($this->user)
        );

        $oriRatingDto = new CreateRatingData(
            rating: 2,
            comment: 'test-abc-123',
            user_id: $this->user->id,
            cocktail_id: $cocktail->id
        );

        app(RateCocktailAction::class)->execute($oriRatingDto);

        $updateRatingDto = new CreateRatingData(
            rating: 5,
            comment: 'test-def-456',
            user_id: $this->user->id,
            cocktail_id: $cocktail->id
        );

        app(RateCocktailAction::class)->execute($updateRatingDto);

        $this->assertDatabaseCount('ratings', 1);

        $this->assertDatabaseHas('ratings', [
            'rating' => 5,
            'comment' => 'test-def-456',
            'user_id' => $this->user->id,
            'cocktail_id' => $cocktail->id
        ]);
    }

    #[Test, Group('cocktails'), Group('cocktail-rating')]
    public function it_rolls_back_on_error(): void
    {
        $cocktail = $this->createCocktail(
            $this->makeCocktail($this->user)
        );

        $ratingDto = new CreateRatingData(
            rating: 2,
            comment: 'test-abc-123',
            user_id: 999,                    // Invalid data
            cocktail_id: $cocktail->id
        );

        $this->expectException(QueryException::class);

        app(RateCocktailAction::class)->execute($ratingDto);

        $this->assertDatabaseCount('ratings', 0);
    }
}
