<?php

namespace App\Actions\Image;

use App\Contracts\HasImage;
use App\Enums\ImageMimeType;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UploadImageAction
{
    private const STORAGE_DISK = 'public';
    private const FOLDER = 'images';


    public function execute(HasImage $model, UploadedFile $file): Image
    {
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, ImageMimeType::values(), true)) {
            throw new \DomainException('Mime type is not supported.');
        }

       $filePath = Storage::disk(self::STORAGE_DISK)->putFileAs(
           self::FOLDER,
           $file,
           $file->hashName()
       );

       if (! $filePath){
           throw new \RuntimeException('The image could not be written to disk. Image was not uploaded.');
       }

        /** @var Image|null $image */
        $image = $model->image()->first();

       if ($image){

           $oldPath = $image->path;
           $oldDisk = $image->disk;

           $image->update([
               'disk' => self::STORAGE_DISK,
               'path' => $filePath,
               'mime_type' => $mimeType,
               'size' => $file->getSize(),
           ]);

           $this->deleteFromFileStorageByPath($oldDisk, $oldPath);

       } else {

           $image = $model->image()->create([
               'disk' => self::STORAGE_DISK,
               'path' => $filePath,
               'mime_type' => $mimeType,
               'size' => $file->getSize(),
           ]);
       }

        /** @var Image $image */
       return $image;
    }

    private function deleteFromFileStorageByPath(?string $disk, ?string $path): void
    {
        if ($disk && $path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
