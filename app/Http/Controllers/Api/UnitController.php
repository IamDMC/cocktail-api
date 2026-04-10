<?php

namespace App\Http\Controllers\Api;

use App\Enums\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
#[Group('Units', description: 'Available measurement units')]
class UnitController extends Controller
{
    #[Response([
        ['name' => 'CL', 'value' => 'cl'],
        ['name' => 'ML', 'value' => 'ml'],
    ])]
    public function __invoke(Request $request): Collection
    {
        return collect(Unit::cases())->map(fn ($unit) => [
            'name' => $unit->name,
            'value' => $unit->value,
        ]);
    }
}
