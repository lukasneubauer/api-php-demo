<?php

declare(strict_types=1);

namespace App\PersisterErrors;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\CouldNotPersistException;

class CouldNotGenerateUniqueValue
{
    /**
     * @throws CouldNotPersistException
     */
    public function throwException(string $property, int $tries): void
    {
        $data = Error::couldNotGenerateUniqueValue($property, $tries);
        $message = \sprintf(Emsg::COULD_NOT_GENERATE_UNIQUE_VALUE, $property, $tries);
        throw new CouldNotPersistException($data, $message);
    }
}
