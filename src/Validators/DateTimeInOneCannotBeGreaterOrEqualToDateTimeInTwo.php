<?php

declare(strict_types=1);

namespace App\Validators;

use App\DateTime\DateTimeUTC;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo
{
    private DateTimeUTC $dateTimeUTC;

    private string $propertyOne;

    private string $propertyTwo;

    public function __construct(DateTimeUTC $dateTimeUTC, string $propertyOne, string $propertyTwo)
    {
        $this->dateTimeUTC = $dateTimeUTC;
        $this->propertyOne = $propertyOne;
        $this->propertyTwo = $propertyTwo;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfDateTimeInOneIsGreaterOrEqualToDateTimeInTwo(array $data): void
    {
        $dateTimeStringOne = $data[$this->propertyOne];
        $dateTimeStringTwo = $data[$this->propertyTwo];
        $dateTimeOne = $this->dateTimeUTC->createDateTimeInstance($dateTimeStringOne);
        $dateTimeTwo = $this->dateTimeUTC->createDateTimeInstance($dateTimeStringTwo);
        $timestampOne = $dateTimeOne->getTimestamp();
        $timestampTwo = $dateTimeTwo->getTimestamp();
        if ($timestampOne >= $timestampTwo) {
            $error = Error::dateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo($this->propertyOne, $this->propertyTwo);
            $message = \sprintf(Emsg::DATETIME_IN_ONE_CANNOT_BE_GREATER_OR_EQUAL_TO_DATETIME_IN_TWO, $this->propertyOne, $this->propertyTwo);
            throw new ValidationException($error, $message);
        }
    }
}
