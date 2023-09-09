<?php

declare(strict_types=1);

return [
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle::class => ['dev' => true, 'dev_no_request_validation' => true, 'test' => true, 'test_with_profiler' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'dev_no_request_validation' => true, 'test' => true, 'test_with_profiler' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'dev_no_request_validation' => true, 'test' => true, 'test_with_profiler' => true],
];
