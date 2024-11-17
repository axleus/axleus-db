<?php

declare(strict_types=1);

namespace Axleus\Db;

use Laminas\Db\ResultSet\AbstractResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Hydrator\HydratorAwareTrait;

abstract class AbstractRepository implements RepositoryInterface
{
    use HydratorAwareTrait;

    public function __construct(
        protected TableGateway $gateway
    ) {
    }

    public function findBy(
        string $column,
        mixed $value,
        ?array $columns = [Select::SQL_STAR],
        ?array $joins = null,
        ?bool $all = false
    ): ResultSetInterface|EntityInterface|array|null {
        if ($all) {
            return $this->findAllBy($column, $value, $columns, $joins);
        }
        return $this->findOneBy($column, $value, $columns, $joins);
    }

    public function findOneBy(
        string $column,
        mixed $value,
        ?array $columns = [Select::SQL_STAR],
        ?array $joins = null
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
        ?array $columns = [Select::SQL_STAR],
        ?array $joins = null
    ): ResultSetInterface|array|null {

        $sql = $this->gateway->getSql();
        $select = $sql->select();
        $select->columns($columns);
        $where = new Where();
        $where->equalTo($column, $value);
        $select->where($where);
        /** @var AbstractResultset */
        $resultSet = $this->gateway->selectWith($select);
        return $resultSet;
    }

    // not overloaded
    public function findAll(bool $returnArray = false): ResultSetInterface|array
    {
        /** @var AbstractResultSet */
        $result = $this->gateway->select();
        if ($returnArray) {
            return $result->toArray();
        }
        return $result;
    }

    /**
     *
     * @param EntityInterface|array $entity
     * @return EntityInterface|int
     * @throws InvalidArgumentException
     * @deprecated
     */
    public function save(
        EntityInterface|array $entity,
        ?string $primaryKey = null,
        ?array $joins = null,
        ?bool $returnArray = false
    ): EntityInterface|int {

        $set = [];
        if ($entity instanceof EntityInterface) {
           $set  = $this->hydrator->extract($entity);
        } elseif (is_array($entity)) {
            $set = $entity;
        }
        if ($set === []) {
            throw new Exception\InvalidArgumentException('Repository can not save empty entity.');
        }
        try {
            if (! isset($set[$primaryKey]) ) {
                // insert
                $this->gateway->insert($set);
                $set['id'] = $this->gateway->getLastInsertValue();
            } else {
                if ($primaryKey === null) {
                    throw Exception\InvalidArgumentException::invalidPrimaryKey(
                        static::class,
                        __METHOD__,
                        $primaryKey
                    );
                }
                $where = new Where();
                // update
                $this->gateway->update(
                    $set,
                    $where->equalTo($primaryKey, $set[$primaryKey]),
                    $joins ?? null
                );
            }
        } catch (Exception\InvalidArgumentException $e) {
            // log this?
            throw $e;
        }
        if ($returnArray) {
            return $set;
        }
        if (is_array($entity)) {
            $entity = $this->gateway->getResultSetPrototype();
        }
        return $this->hydrator->hydrate($set, $entity);
    }
}
