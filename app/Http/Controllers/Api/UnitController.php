<?php

namespace App\Http\Controllers\Api;

use App\Enums\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UnitController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Collection
    {
        return collect(Unit::cases())->map(fn ($unit) => [
            'name' => $unit->name,
            'value' => $unit->value,
        ]);
    }
}
