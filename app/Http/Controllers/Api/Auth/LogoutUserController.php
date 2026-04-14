<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Auth')]
class LogoutUserController extends Controller
{
    #[Authenticated]

    #[Response([
        'message' => 'Logged out'
    ], status: 200)]
    public function __invoke(Request $request)
    {
        // delete token and logout by doing so
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out'
        ]);
    }
}
