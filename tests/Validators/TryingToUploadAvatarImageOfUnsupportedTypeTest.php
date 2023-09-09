<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Base64\Base64Decoder;
use App\Checks\ImageCheck;
use App\Exceptions\ValidationException;
use App\Validators\TryingToUploadAvatarImageOfUnsupportedType;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class TryingToUploadAvatarImageOfUnsupportedTypeTest extends TestCase
{
    public function testCheckIfTryingToUploadAvatarImageOfUnsupportedTypeDoesNotThrowException(): void
    {
        try {
            $base64Decoder = m::mock(Base64Decoder::class)
                ->shouldReceive('decode')
                ->times(1)
                ->andReturn('SOURCE-STRING')
                ->getMock();
            $imageCheck = m::mock(ImageCheck::class)
                ->shouldReceive('isImageTypeSupported')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $validator = new TryingToUploadAvatarImageOfUnsupportedType($base64Decoder, $imageCheck);
            $validator->checkIfTryingToUploadAvatarImageOfUnsupportedType(['avatar' => 'BASE64-STRING']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfTryingToUploadAvatarImageOfUnsupportedTypeThrowsException(): void
    {
        try {
            $base64Decoder = m::mock(Base64Decoder::class)
                ->shouldReceive('decode')
                ->times(1)
                ->andReturn('SOURCE-STRING')
                ->getMock();
            $imageCheck = m::mock(ImageCheck::class)
                ->shouldReceive('isImageTypeSupported')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $validator = new TryingToUploadAvatarImageOfUnsupportedType($base64Decoder, $imageCheck);
            $validator->checkIfTryingToUploadAvatarImageOfUnsupportedType(['avatar' => 'BASE64-STRING']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(66, $data['error']['code']);
            $this->assertSame('Trying to upload avatar image of unsupported type.', $data['error']['message']);
            $this->assertSame('Trying to upload avatar image of unsupported type.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
