<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MissingMandatoryHttpHeader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class MissingMandatoryHttpHeaderTest extends TestCase
{
    public function testCheckIfHttpHeaderIsMissingDoesNotThrowException(): void
    {
        try {
            $validator = new MissingMandatoryHttpHeader('EXPECTED-HEADER');
            $validator->checkIfHttpHeaderIsMissing(new HeaderBag(['EXPECTED-HEADER' => '']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfHttpHeaderIsMissingThrowsException(): void
    {
        try {
            $validator = new MissingMandatoryHttpHeader('EXPECTED-HEADER');
            $validator->checkIfHttpHeaderIsMissing(new HeaderBag());
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(1, $data['error']['code']);
            $this->assertSame("Missing mandatory 'EXPECTED-HEADER' http header.", $data['error']['message']);
            $this->assertSame("Missing mandatory 'EXPECTED-HEADER' http header.", $e->getMessage());
        }
    }
}
