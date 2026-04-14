<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Auth')]
class LoginUserController extends Controller
{
    #[Unauthenticated]

    #[BodyParam('email', 'string', 'User email address', example: 'test@example.com')]
    #[BodyParam('password', 'string', 'User password', example: 'Password123!')]

    #[Response([
        'token' => '1|abc123tokenexample',
        'user' => [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Max Mustermann',
        ],
    ], status: 200)]

    #[Response([
        'message' => 'Email or password are incorrect.'
    ], status: 401)]

    #[Response([
        'message' => 'Email is not verified.'
    ], status: 403)]
    public function __invoke(LoginUserRequest $request)
    {
        $credentials = $request->validated();

        $email = strtolower($credentials['email']);

        $user = User::where('email', $email)->first();

        // Auth::attempt is using session, therefore manual check works better
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthorizationException('Email or password are incorrect.');
        }

        // User must be verified
        if (! $user->hasVerifiedEmail()) {
            throw new AuthorizationException('Email is not verified.');
        }

        // Bearer Token
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
