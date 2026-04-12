<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/categories',\App\Http\Controllers\Api\CategoryController::class);

Route::apiResource('/ingredients', \App\Http\Controllers\Api\IngredientController::class);

Route::apiResource('/cocktails', \App\Http\Controllers\Api\CocktailController::class);

Route::get('/units', \App\Http\Controllers\Api\UnitController::class);

Route::post('/rating/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailRatingController::class, 'store']);
Route::put('/rating/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailRatingController::class, 'update']);

Route::post('/favorite/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailFavoredByController::class, 'store']);
Route::delete('/favorite/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailFavoredByController::class, 'destroy']);
