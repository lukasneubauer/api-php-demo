<?php

declare(strict_types=1);

namespace App\Images;

use Nette\Utils\Image;
use Nette\Utils\ImageException;

class ImageFactory
{
    /**
     * @throws ImageException
     */
    public function createImage(string $sourceString): Image
    {
        return Image::fromString($sourceString);
    }
}
