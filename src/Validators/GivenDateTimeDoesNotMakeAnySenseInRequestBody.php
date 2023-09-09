<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class GivenDateTimeDoesNotMakeAnySenseInRequestBody
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfGivenDateTimeMakesAnySense(array $data): void
    {
        $dateTimeString = $data[$this->property];
        $dateTime = \str_replace(['-', ':'], ' ', $dateTimeString);
        $dateTimeParts = \explode(' ', $dateTime);

        $year = (int) $dateTimeParts[0];
        $month = (int) $dateTimeParts[1];
        $day = (int) $dateTimeParts[2];
        $hour = (int) $dateTimeParts[3];
        $minute = (int) $dateTimeParts[4];
        $second = (int) $dateTimeParts[5];

        $isDateValid = \checkdate($month, $day, $year);

        $isTimeValid = ($hour >= 0 && $hour < 24)
            && ($minute >= 0 && $minute < 60)
            && ($second >= 0 && $second < 60);

        if ($isDateValid === false || $isTimeValid === false) {
            $error = Error::givenDateTimeDoesNotMakeAnySense($dateTimeString);
            $message = \sprintf(Emsg::GIVEN_DATETIME_DOES_NOT_MAKE_ANY_SENSE, $dateTimeString);
            throw new ValidationException($error, $message);
        }
    }
}
