<?php

declare(strict_types=1);

namespace Axleus\Db;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Hydrator\HydratorAwareInterface;

interface RepositoryInterface extends HydratorAwareInterface
{
    public function findBy(
        string $column,
        mixed $value,
        ?array $columns = ['*'],
        ?array $joins = null,
        ?bool $all = false
    ): ResultSetInterface|EntityInterface|array|null;

    public function findOneBy(
        string $column,
        mixed $value,
        ?array $columns = ['*'],
        ?array $joins   = null
    ): ?EntityInterface;

    public function findAllBy(
        string $column,
        mixed $value,
        ?array $columns = ['*'],
        ?array $joins = null
    ): ResultSetInterface|array|null;

}
