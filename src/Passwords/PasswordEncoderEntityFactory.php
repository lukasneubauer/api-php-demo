<?php

declare(strict_types=1);

namespace App\Passwords;

use App\Entities\Password;
use App\EntityFactories\PasswordFactory;
use App\Exceptions\PasswordHashingFailedException;

class PasswordEncoderEntityFactory
{
    private PasswordEncoder $passwordEncoder;

    private PasswordFactory $passwordFactory;

    private PasswordSettings $passwordSettings;

    public function __construct(
        PasswordEncoder $passwordEncoder,
        PasswordFactory $passwordFactory,
        PasswordSettings $passwordSettings
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->passwordFactory = $passwordFactory;
        $this->passwordSettings = $passwordSettings;
    }

    /**
     * @throws PasswordHashingFailedException
     */
    public function createPassword(string $plainPassword): Password
    {
        $passwordHash = $this->passwordEncoder->hashPassword($plainPassword);
        $passwordAlgorithm = $this->passwordSettings->getAlgorithm();
        $password = $this->passwordFactory->create($passwordHash, $passwordAlgorithm);

        return $password;
    }
}
