<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MissingValueForUrlParameter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class MissingValueForUrlParameterTest extends TestCase
{
    public function testCheckIfUrlParameterIsEmptyDoesNotThrowException(): void
    {
        try {
            $validator = new MissingValueForUrlParameter('expectedParameter');
            $validator->checkIfUrlParameterIsEmpty(new ParameterBag(['expectedParameter' => '1234567890']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUrlParameterIsEmptyThrowsException(): void
    {
        try {
            $validator = new MissingValueForUrlParameter('expectedParameter');
            $validator->checkIfUrlParameterIsEmpty(new ParameterBag(['expectedParameter' => '']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(6, $data['error']['code']);
            $this->assertSame("Missing value for 'expectedParameter' url parameter.", $data['error']['message']);
            $this->assertSame("Missing value for 'expectedParameter' url parameter.", $e->getMessage());
        }
    }
}
