<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MissingMandatoryUrlParameter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class MissingMandatoryUrlParameterTest extends TestCase
{
    public function testCheckIfUrlParameterIsMissingDoesNotThrowException(): void
    {
        try {
            $validator = new MissingMandatoryUrlParameter('expectedParameter');
            $validator->checkIfUrlParameterIsMissing(new ParameterBag(['expectedParameter' => '']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUrlParameterIsMissingThrowsException(): void
    {
        try {
            $validator = new MissingMandatoryUrlParameter('expectedParameter');
            $validator->checkIfUrlParameterIsMissing(new ParameterBag());
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(5, $data['error']['code']);
            $this->assertSame("Missing mandatory 'expectedParameter' url parameter.", $data['error']['message']);
            $this->assertSame("Missing mandatory 'expectedParameter' url parameter.", $e->getMessage());
        }
    }
}
