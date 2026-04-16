<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(UserUpdateRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return new UserResource($user);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        $user->delete();

        return response()->noContent();
    }
}
