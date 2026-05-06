<?php

namespace App\Models;

use App\Enums\ImageMimeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $disk
 * @property string $path
 * @property ImageMimeType $mime_type
 * @property int $size
 */
class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'mime_type',
        'size',
        'imageable_type',
        'imageable_id',
    ];

    protected $casts = [
        'mime_type' => ImageMimeType::class
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
