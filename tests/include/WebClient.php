<?php

declare(strict_types=1);

namespace Tests;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\PhpProcess;

final class WebClient extends KernelBrowser
{
    /**
     * @throws RuntimeException
     */
    protected function doRequestInProcess(/* Request */$request): Response
    {
        $process = new PhpProcess($this->getScript($request), null, null);
        $process->run();

        if ($process->isSuccessful() === false || \preg_match('/^O\:\d+\:/', $process->getOutput()) !== 1) {
            ErrorDump::dumpError($process->getOutput());
        }

        return \unserialize($process->getOutput());
    }
}
