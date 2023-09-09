<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\GivenDateTimeDoesNotMakeAnySenseInRequestBody;
use PHPUnit\Framework\TestCase;
use Tests\NonsensicalDateTimeDataProvider;

final class GivenDateTimeDoesNotMakeAnySenseInRequestBodyTest extends TestCase
{
    public function testCheckIfGivenDateTimeMakesAnySenseDoesNotThrowException(): void
    {
        try {
            $validator = new GivenDateTimeDoesNotMakeAnySenseInRequestBody('from');
            $validator->checkIfGivenDateTimeMakesAnySense(['from' => '2000-12-24 20:30:00']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getNonsensicalDateTimes
     */
    public function testCheckIfGivenDateTimeMakesAnySenseThrowsException(string $dateTime): void
    {
        try {
            $validator = new GivenDateTimeDoesNotMakeAnySenseInRequestBody('from');
            $validator->checkIfGivenDateTimeMakesAnySense(['from' => $dateTime]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(20, $data['error']['code']);
            $this->assertSame(\sprintf("Given datetime '%s' does not make any sense.", $dateTime), $data['error']['message']);
            $this->assertSame(\sprintf("Given datetime '%s' does not make any sense.", $dateTime), $e->getMessage());
        }
    }

    public static function getNonsensicalDateTimes(): array
    {
        return NonsensicalDateTimeDataProvider::getNonsensicalDateTimes();
    }
}
