<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

trait Id
{
    /**
     * @ORM\Id
     * @ORM\Column(name="`id`", type="string", length=36, options={"fixed": true})
     */
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }
}
