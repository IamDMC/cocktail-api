<?php

namespace App\Data\Rating;

final readonly class CreateRatingData
{
    /**
     * @param int $rating
     * @param string|null $comment
     * @param int $user_id
     * @param int $cocktail_id
     */
    public function __construct(
        public int $rating,
        public ?string $comment,
        public int $user_id,
        public int $cocktail_id
    ) {}
}
