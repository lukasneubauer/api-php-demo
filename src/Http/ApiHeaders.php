<?php

declare(strict_types=1);

namespace App\Http;

class ApiHeaders
{
    /** @var string */
    public const API_CLIENT_ID = 'Api-Client-Id';

    /** @var int */
    public const API_CLIENT_ID_LENGTH = 40;

    /** @var string */
    public const API_CLIENT_ID_PATTERN = '0-9a-z';

    /** @var string */
    public const API_KEY = 'Api-Key';

    /** @var int */
    public const API_KEY_LENGTH = 120;

    /** @var string */
    public const API_KEY_PATTERN = '0-9a-z';

    /** @var string */
    public const API_TOKEN = 'Api-Token';

    /** @var int */
    public const API_TOKEN_LENGTH = 80;

    /** @var string */
    public const API_TOKEN_PATTERN = '0-9a-z';
}
