<?php

namespace Image;

use App\Actions\Image\UploadImageAction;
use App\Contracts\HasImage;
use App\Models\Cocktail;
use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Cocktail\ImageTestHelper;
use Tests\TestCase;

class UploadImageActionTest extends TestCase
{
    use RefreshDatabase, ImageTestHelper;

    #[Test, Group('image'), Group('cocktails')]
    public function it_creates_cocktail_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('new.jpg');

        $cocktail = Cocktail::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $image = app(UploadImageAction::class)->execute($cocktail, $file);

        $this->assertNotNull($image);
        Storage::disk('public')->assertExists($image->path);
    }

    #[Test, Group('image'), Group('cocktails')]
    public function it_updates_cocktail_image()
    {
        Storage::fake('public');

        $cocktail = Cocktail::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->fakeHasImage($cocktail, 'old.jpeg', 'image/jpeg');


        $file = UploadedFile::fake()->image('new.png');

        $image = app(UploadImageAction::class)->execute($cocktail, $file);

        $this->assertNotNull($image);
        Storage::disk('public')->assertExists($image->path);
    }

    #[Test, Group('image'), Group('users')]
    public function it_creates_user_profile_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('new.jpg');

        $user = User::factory()->create();

        $image = app(UploadImageAction::class)->execute($user, $file);

        $this->assertNotNull($image);
        Storage::disk('public')->assertExists($image->path);
    }

    #[Test, Group('image'), Group('users')]
    public function it_updates_user_profile_image()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->fakeHasImage($user, 'old.jpeg', 'image/jpeg');


        $file = UploadedFile::fake()->image('new.png');

        $image = app(UploadImageAction::class)->execute($user, $file);

        $this->assertNotNull($image);
        Storage::disk('public')->assertExists($image->path);
    }

    #[Test, Group('image')]
    public function it_throws_exception_if_mime_type_is_not_supported()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('profile.jpg', 100, 'application/pdf');           // Invalid data

        $user = User::factory()->create();

        $this->expectException(\DomainException::class);

        app(UploadImageAction::class)->execute($user, $file);
    }

    #[Test, Group('image')]
    public function it_throws_exception_if_file_cannot_be_written()
    {
        Storage::fake('public');

        Storage::shouldReceive('disk')
            ->with('public')
            ->andReturnSelf();

        Storage::shouldReceive('putFileAs')
            ->andReturn(false);

        $cocktail = Cocktail::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $file = UploadedFile::fake()->image('test.jpg');

        $this->expectException(\RuntimeException::class);

        app(UploadImageAction::class)->execute($cocktail, $file);
    }

    #[Test, Group('image')]
    public function it_deletes_old_image_when_updating()
    {
        Storage::fake('public');

        $cocktail = Cocktail::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $oldImage = $this->fakeHasImage($cocktail, 'old.jpg', 'image/jpeg');

        $file = UploadedFile::fake()->image('new.png');

        $image = app(UploadImageAction::class)->execute($cocktail, $file);

        Storage::disk('public')->assertMissing($oldImage->path);
        Storage::disk('public')->assertExists($image->path);
    }

    #[Test, Group('image')]
    public function it_updates_image_metadata()
    {
        Storage::fake('public');

        $cocktail = Cocktail::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->fakeHasImage($cocktail, 'old.jpg', 'image/jpeg');

        $file = UploadedFile::fake()->image('new.png');

        $image = app(UploadImageAction::class)->execute($cocktail, $file);

        $this->assertDatabaseHas('images', [
            'id' => $image->id,
            'mime_type' => 'image/png',
            'disk' => 'public',
        ]);
    }
}
