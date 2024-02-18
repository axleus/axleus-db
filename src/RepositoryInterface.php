<?php

declare(strict_types=1);

namespace Axleus\Db;

use Laminas\Db\ResultSet\ResultSetInterface;

interface RepositoryInterface
{
    public function findBy(string $column, mixed $value, ?array $columns = ['*'], ?bool $all = false): ResultSetInterface|EntityInterface|array|null;
    public function findOneBy(string $column, mixed $value, ?array $columns = ['*']): ?EntityInterface;
    public function findAllBy(string $column, mixed $value, ?array $columns = ['*']): ResultSetInterface|array;
    public function save(EntityInterface|array $entity): EntityInterface|int;
}
