<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MissingValueForHttpHeader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class MissingValueForHttpHeaderTest extends TestCase
{
    public function testCheckIfHttpHeaderIsEmptyDoesNotThrowException(): void
    {
        try {
            $validator = new MissingValueForHttpHeader('EXPECTED-HEADER');
            $validator->checkIfHttpHeaderIsEmpty(new HeaderBag(['EXPECTED-HEADER' => '1234567890']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfHttpHeaderIsEmptyThrowsException(): void
    {
        try {
            $validator = new MissingValueForHttpHeader('EXPECTED-HEADER');
            $validator->checkIfHttpHeaderIsEmpty(new HeaderBag(['EXPECTED-HEADER' => '']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(2, $data['error']['code']);
            $this->assertSame("Missing value for 'EXPECTED-HEADER' http header.", $data['error']['message']);
            $this->assertSame("Missing value for 'EXPECTED-HEADER' http header.", $e->getMessage());
        }
    }
}
