<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * public routes
 */
Route::post('/auth/register', \App\Http\Controllers\Api\Auth\RegisterUserController::class);
Route::post('/auth/login', \App\Http\Controllers\Api\Auth\LoginUserController::class)->middleware('throttle:5,1');

// Email verification route
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect(config('app.frontend_url') . '/verified');

})->middleware(['signed'])->name('verification.verify');


/**
 * Authenticated routes
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::post('/auth/logout', \App\Http\Controllers\Api\Auth\LogoutUserController::class);

    // Resend email verification link
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent'
        ]);
    })->middleware('throttle:3,1');
});

/**
 * Authenticated and email verified routes
 */
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::apiResource('/categories',\App\Http\Controllers\Api\CategoryController::class);

    Route::apiResource('/ingredients', \App\Http\Controllers\Api\IngredientController::class);

    Route::apiResource('/cocktails', \App\Http\Controllers\Api\CocktailController::class);

    Route::get('/units', \App\Http\Controllers\Api\UnitController::class);

    Route::post('/rating/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailRatingController::class, 'store']);
    Route::put('/rating/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailRatingController::class, 'update']);

    Route::post('/favorite/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailFavoredByController::class, 'store']);
    Route::delete('/favorite/cocktails/{cocktail}', [\App\Http\Controllers\Api\CocktailFavoredByController::class, 'destroy']);
});
