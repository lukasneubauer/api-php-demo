<?php

declare(strict_types=1);

namespace Tests\App\Images;

use App\Images\ImageFactory;
use Nette\Utils\Image;
use Nette\Utils\ImageException;
use PHPUnit\Framework\TestCase;

final class ImageFactoryTest extends TestCase
{
    /**
     * @dataProvider getData
     *
     * @throws ImageException
     */
    public function testCreateImageDoesNotThrowException(string $fileName): void
    {
        $imageFactory = new ImageFactory();
        $image = $imageFactory->createImage(\file_get_contents(__DIR__ . '/../../resources/images/' . $fileName));
        $this->assertInstanceOf(Image::class, $image);
    }

    public static function getData(): array
    {
        return [
            [
                '1x1.gif',
            ],
            [
                '1x1.jpeg',
            ],
            [
                '1x1.jpg',
            ],
            [
                '1x1.png',
            ],
        ];
    }

    public function testCreateImageThrowsException(): void
    {
        try {
            $imageFactory = new ImageFactory();
            $imageFactory->createImage(\file_get_contents(__DIR__ . '/../../resources/images/1x1.xcf'));
            $this->fail('Failed to throw exception.');
        } catch (ImageException $e) {
            $this->assertTrue(true);
        }
    }
}
