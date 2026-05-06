<?php

namespace App\Enums;

Enum ImageMimeType: string
{
    case JPEG = 'image/jpeg';
    case PNG = 'image/png';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
