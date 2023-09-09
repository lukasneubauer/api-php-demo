<?php

declare(strict_types=1);

namespace App\Avatars;

use App\Images\ImageFactory;
use Nette\Utils\Image;
use Nette\Utils\ImageException;

class AvatarCreator
{
    private ImageFactory $imageFactory;

    public function __construct(ImageFactory $imageFactory)
    {
        $this->imageFactory = $imageFactory;
    }

    /**
     * @throws ImageException
     */
    public function create(string $sourceString): string
    {
        $image = $this->imageFactory->createImage($sourceString);
        $image->resize(256, 256, Image::Cover);

        $blank = Image::fromBlank(
            256,
            256,
            Image::rgb(
                255,
                255,
                255
            )
        );

        $blank->place($image);

        return $blank->toString(Image::PNG, 9);
    }
}
