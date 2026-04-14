<?php

namespace Tests\Feature\Auth\Actions;

use App\Actions\Auth\CreateUserAction;
use App\Data\User\CreateUserData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('auth')]
    public function it_creates_user():void
    {
        $userData = [
            'email' => 'test@test.at',
            'password' => 'password',
            'name' => 'Maxi',
        ];

        $userDto = new CreateUserData(
            email: $userData['email'],
            password: $userData['password'],
            name: $userData['name']
        );

        $user = app(CreateUserAction::class)->execute($userDto);

        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name' => $userData['name'],
        ]);

        $this->assertTrue(
            Hash::check($userData['password'], $user->password)
        );
    }
}
