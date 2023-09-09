<?php

declare(strict_types=1);

namespace Tests\App\Avatars;

use App\Avatars\AvatarCreator;
use App\Images\ImageFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AvatarCreatorFunctionalTest extends KernelTestCase
{
    /**
     * @dataProvider getData
     */
    public function testCreate(string $fileName): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        /** @var AvatarCreator $avatarCreator */
        $avatarCreator = $dic->get(AvatarCreator::class);

        /** @var ImageFactory $imageFactory */
        $imageFactory = $dic->get(ImageFactory::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/' . $fileName);

        $avatarString = $avatarCreator->create($sourceString);

        $image = $imageFactory->createImage($avatarString);

        $this->assertSame(256, $image->getWidth());
        $this->assertSame(256, $image->getHeight());
    }

    public static function getData(): array
    {
        return [
            [
                '256x256.gif',
            ],
            [
                '256x256.jpeg',
            ],
            [
                '256x256.jpg',
            ],
            [
                '256x256.png',
            ],
        ];
    }
}
