<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\CreateUserAction;
use App\Data\User\CreateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Auth\Events\Registered;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;
#[Group('Auth')]
class RegisterUserController extends Controller
{
    #[Unauthenticated]

    #[BodyParam('email', 'string', 'User email address', example: 'test@example.com')]
    #[BodyParam('password', 'string', 'User password (must be confirmed)', example: 'Password123!')]
    #[BodyParam('password_confirmation', 'string', 'Password confirmation', example: 'Password123!')]
    #[BodyParam('name', 'string', 'User name', example: 'Max Mustermann')]

    #[Response([
        'message' => 'User registered. Please verify your email.'
    ], status: 201)]

    public function __invoke(RegisterUserRequest $request)
    {
        $data = $request->validated();

        $createUserDto = new CreateUserData(
            email: $data['email'],
            password: $data['password'],
            name: $data['name']
        );

        $user = app(CreateUserAction::class)->execute($createUserDto);

        event(new Registered($user));

        return response()->json([
            'message' => 'User registered. Please verify your email.'
        ], 201);
    }
}
