<?php

namespace Tests\Support\Cocktail;


use App\Contracts\HasImage;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait ImageTestHelper {

    public function fakeHasImage(HasImage $model, string $imageName, string $mimeType): Image
    {
        $file = UploadedFile::fake()->image($imageName);

        $path = Storage::disk('public')->putFileAs(
            'images',
            $file,
            $imageName
        );

        return $model->image()->create([
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
        ]);
    }
}
