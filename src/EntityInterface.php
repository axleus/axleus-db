<?php

/**
 * This interface does not extend the Permission Acl interfaces so
 * that developers can implement them via the concrete classes
 */

declare(strict_types=1);

namespace Axleus\Db;

use Laminas\Hydrator\HydratorAwareInterface;


interface EntityInterface
{
    public function getId(): ?int;
    public function toArray(): array;
    public function fromArray(array $data): void;
}
