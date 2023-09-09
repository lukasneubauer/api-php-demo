<?php

declare(strict_types=1);

namespace App\Validators;

use App\Base64\Base64Decoder;
use App\Checks\ImageCheck;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class TryingToUploadAvatarImageOfUnsupportedType
{
    private Base64Decoder $base64Decoder;

    private ImageCheck $imageCheck;

    public function __construct(Base64Decoder $base64Decoder, ImageCheck $imageCheck)
    {
        $this->base64Decoder = $base64Decoder;
        $this->imageCheck = $imageCheck;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfTryingToUploadAvatarImageOfUnsupportedType(array $data): void
    {
        $sourceString = $this->base64Decoder->decode($data['avatar']);
        $isImageTypeSupported = $this->imageCheck->isImageTypeSupported($sourceString);

        if ($isImageTypeSupported === false) {
            $error = Error::tryingToUploadAvatarImageOfUnsupportedType();
            $message = Emsg::TRYING_TO_UPLOAD_AVATAR_IMAGE_OF_UNSUPPORTED_TYPE;
            throw new ValidationException($error, $message);
        }
    }
}
