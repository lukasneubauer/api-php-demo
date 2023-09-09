<?php

declare(strict_types=1);

namespace Tests\App\Checks;

use App\Checks\ImageCheck;
use PHPUnit\Framework\TestCase;

final class ImageCheckTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testIsImageTypeSupported(string $image, bool $expectedSupport): void
    {
        $imageCheck = new ImageCheck();
        $isImageTypeSupported = $imageCheck->isImageTypeSupported(\file_get_contents(__DIR__ . '/../../resources/images/' . $image));
        $this->assertSame($expectedSupport, $isImageTypeSupported);
    }

    public static function getData(): array
    {
        return [
            [
                '1x1.gif',
                true,
            ],
            [
                '1x1.jpeg',
                true,
            ],
            [
                '1x1.jpg',
                true,
            ],
            [
                '1x1.png',
                true,
            ],
            [
                '1x1.xcf',
                false,
            ],
        ];
    }
}
