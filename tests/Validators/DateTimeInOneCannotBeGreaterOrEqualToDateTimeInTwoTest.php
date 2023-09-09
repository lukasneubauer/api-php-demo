<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\DateTime\DateTimeUTC;
use App\Exceptions\ValidationException;
use App\Validators\DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo;
use PHPUnit\Framework\TestCase;

final class DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwoTest extends TestCase
{
    public function testCheckIfDateTimeInOneIsGreaterOrEqualToDateTimeInTwoDoesNotThrowException(): void
    {
        try {
            $validator = new DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo(new DateTimeUTC(), 'from', 'to');
            $validator->checkIfDateTimeInOneIsGreaterOrEqualToDateTimeInTwo(
                [
                    'from' => '2000-01-01 16:00:00',
                    'to' => '2000-01-01 18:00:00',
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getDateTimes
     */
    public function testCheckIfDateTimeInOneIsGreaterOrEqualToDateTimeInTwoThrowsException(string $dateTimeOne, string $dateTimeTwo): void
    {
        try {
            $validator = new DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo(new DateTimeUTC(), 'from', 'to');
            $validator->checkIfDateTimeInOneIsGreaterOrEqualToDateTimeInTwo(
                [
                    'from' => $dateTimeOne,
                    'to' => $dateTimeTwo,
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(21, $data['error']['code']);
            $this->assertSame("Datetime in 'from' cannot be greater or equal to datetime in 'to'.", $data['error']['message']);
            $this->assertSame("Datetime in 'from' cannot be greater or equal to datetime in 'to'.", $e->getMessage());
        }
    }

    public static function getDateTimes(): array
    {
        return [
            ['2000-01-01 18:00:00', '2000-01-01 16:00:00'],
            ['2000-01-01 16:00:00', '2000-01-01 16:00:00'],
        ];
    }
}
