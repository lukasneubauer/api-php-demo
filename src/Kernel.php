<?php

declare(strict_types=1);

namespace App;

use Exception;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function registerBundles(): iterable
    {
        $bundlesToRegister = [];

        $bundles = require $this->getProjectDir() . '/config/bundles.php';

        foreach ($bundles as $class => $envs) {
            if ((\array_key_exists($this->environment, $envs) && $envs[$this->environment] === true)
                || (\array_key_exists('all', $envs) && $envs['all'] === true)
            ) {
                $bundlesToRegister[] = new $class();
            }
        }

        return $bundlesToRegister;
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/config/config_' . $this->getEnvironment() . '.yaml');
    }
}
