<?php

declare(strict_types=1);

namespace App\PersisterErrors;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\CouldNotPersistException;

class ValueIsAlreadyTaken
{
    /**
     * @throws CouldNotPersistException
     */
    public function throwException(string $property): void
    {
        $data = Error::valueIsAlreadyTaken($property);
        $message = \sprintf(Emsg::VALUE_IS_ALREADY_TAKEN, $property);
        throw new CouldNotPersistException($data, $message);
    }
}
