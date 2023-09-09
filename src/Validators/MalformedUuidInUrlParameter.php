<?php

declare(strict_types=1);

namespace App\Validators;

use App\Checks\UuidCheck;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;

class MalformedUuidInUrlParameter
{
    private UuidCheck $uuidCheck;

    private string $urlParameter;

    public function __construct(UuidCheck $uuidCheck, string $urlParameter)
    {
        $this->uuidCheck = $uuidCheck;
        $this->urlParameter = $urlParameter;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUuidIsMalformed(ParameterBag $parameters): void
    {
        if ($this->uuidCheck->isUuidValid($parameters->get($this->urlParameter)) === false) {
            $data = Error::malformedUuid();
            $message = \sprintf(Emsg::MALFORMED_UUID);
            throw new ValidationException($data, $message);
        }
    }
}
