<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MalformedDateTimeInRequestBody;
use PHPUnit\Framework\TestCase;
use Tests\MalformedDateTimeDataProvider;

final class MalformedDateTimeInRequestBodyTest extends TestCase
{
    public function testCheckIfDateTimeIsMalformedDoesNotThrowException(): void
    {
        try {
            $validator = new MalformedDateTimeInRequestBody('from');
            $validator->checkIfDateTimeIsMalformed(['from' => '2000-12-24 20:30:00']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getMalformedDateTimes
     */
    public function testCheckIfDateTimeIsMalformedThrowsException(string $dateTime): void
    {
        try {
            $validator = new MalformedDateTimeInRequestBody('from');
            $validator->checkIfDateTimeIsMalformed(['from' => $dateTime]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(17, $data['error']['code']);
            $this->assertSame("Malformed datetime in 'from'. Expected string e.g. '2000-12-24 20:30:00'.", $data['error']['message']);
            $this->assertSame("Malformed datetime in 'from'. Expected string e.g. '2000-12-24 20:30:00'.", $e->getMessage());
        }
    }

    public static function getMalformedDateTimes(): array
    {
        return MalformedDateTimeDataProvider::getMalformedDateTimes();
    }
}
