<?php

declare(strict_types=1);

namespace Axleus\Db;

use Laminas\Db\ResultSet\AbstractResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Where;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\ReflectionHydrator;

class AbstractRepository implements RepositoryInterface, RepositoryCommandInterface
{
    use RepositoryTrait;

    public function __construct(
        private TableGateway $gateway,
        private HydratorInterface $hydrator = new ReflectionHydrator(),
    ) {
    }

    public function findBy(
        string $column,
        mixed $value,
        ?array $columns = ['*'],
        ?bool $all = false
    ): ResultSetInterface|EntityInterface|array|null {
        if ($all) {
            return $this->findAllBy($column, $value);
        }
        return $this->findOneBy($column, $value);
    }

    public function findOneBy(
        string $column,
        mixed $value,
        ?array $columns = ['*']
    ): ?EntityInterface {
        $where = new Where();
        $where->equalTo($column, $value);
        /** @var AbstractResultset */
        $resultSet = $this->gateway->select($where);
        return $resultSet->current();
    }

    public function findAllBy(
        string $column,
        mixed $value,
        ?array $columns = ['*']
    ): ResultSetInterface|array {
        $where = new Where();
        $where->equalTo($column, $value);
        /** @var AbstractResultset */
        $resultSet = $this->gateway->select($where);
        return $resultSet;
    }
}
