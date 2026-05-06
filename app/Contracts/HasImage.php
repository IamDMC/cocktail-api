<?php

namespace App\Contracts;

use App\Models\Image;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read Image|null $image
 */
interface HasImage {

    /**
     * @return MorphOne
     */
    public function image(): MorphOne;
}
