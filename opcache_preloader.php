<?php

declare(strict_types=1);

$file = __DIR__ . '/var/cache/prod/App_KernelProdContainer.preload.php';

if (\file_exists($file) === true) {
    require_once $file;
}
