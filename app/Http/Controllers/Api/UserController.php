<?php

namespace App\Http\Controllers\Api;

use App\Actions\Image\UploadImageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('User', 'Manage authenticated user profile')]
class UserController extends Controller
{
    #[Authenticated]
    #[ResponseFromApiResource(UserResource::class)]
    public function show(Request $request)
    {
        return new UserResource(
            $request->user()->load('image')
        );
    }

    #[Authenticated]
    #[BodyParam('email', 'string', 'User email address', example: 'test@example.com')]
    #[BodyParam('name', 'string', 'User name', example: 'John Doe')]
    #[BodyParam('password', 'string', 'New password (must be confirmed)', example: 'Password1!')]
    #[BodyParam('password_confirmation', 'string', 'Password confirmation', example: 'Password1!')]
    #[BodyParam('image', 'file', 'User profile image (jpeg, png, max 5MB)')]
    #[ResponseFromApiResource(UserResource::class)]
    public function update(UserUpdateRequest $request)
    {
        $user = $request->user();

        $emailChanged = $request->filled('email') && $request->email !== $user->email;

        // Base data update
        $user->fill($request->only(['email', 'name']));

        // Handle password separately
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($request->hasFile('image')) {
            app(UploadImageAction::class)->execute(
                $user,
                $request->file('image')
            );
        }

        return new UserResource($user);
    }
    #[Authenticated]
    public function destroy(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        if ($user->image) {
            Storage::disk($user->image->disk)->delete($user->image->path);
            $user->image()->delete();
        }

        $user->delete();

        return response()->noContent();
    }
}
