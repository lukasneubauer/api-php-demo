<?php

declare(strict_types=1);

namespace App\Checks;

class ImageCheck
{
    public function isImageTypeSupported(string $sourceString): bool
    {
        $info = @\getimagesizefromstring($sourceString);

        if (\is_array($info) === true) {
            $mime = $info['mime'];
            $type = \substr($mime, 6);
            if ($type === 'gif' || $type === 'jpeg' || $type === 'png') {
                return true;
            }
        }

        return false;
    }
}
