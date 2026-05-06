<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
/**
 *
 * @property \App\Models\Image $resource
 */
class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => Storage::disk($this->resource->disk)->url($this->resource->path),
            'mime_type' => $this->resource->mime_type,
            'size' => $this->resource->size,
        ];
    }
}
