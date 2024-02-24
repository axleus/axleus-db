<?php

declare(strict_types=1);

namespace Axleus\Db;

use ReflectionClass;

trait EntityTrait
{

    public function toArray(): array
    {
        return (new ReflectionClass($this))->getProperties();
    }

    public function fromArray(array $data): void
    {
        //$this->getHydrator()->hydrate($data, $this);
    }
}
