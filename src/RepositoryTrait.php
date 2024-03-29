<?php

declare(strict_types=1);

namespace Axleus\Db;

use Closure;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Stdlib\ErrorHandler;
use InvalidArgumentException;

use const E_WARNING;

use function is_array;
use function lcfirst;
use function preg_match;

trait RepositoryTrait
{
    /**
     *
     * @param EntityInterface|array $entity
     * @return EntityInterface|int
     * @throws InvalidArgumentException
     */
    public function save(
        EntityInterface|array $entity,
        ?string $primaryKey = null,
        ?array $joins = null,
        ?bool $returnArray = false
    ): EntityInterface|int {

        $set = [];
        if ($entity instanceof EntityInterface) {
           $set = $this->hydrator->extract($entity);
           $test = $entity->toArray();
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

    public function fetchAll(): ResultSetInterface
    {
        return $this->gateway->select();
    }

    public function delete(?EntityInterface $entity = null, Where|Closure|array|null $where = null): int
    {
        return $this->gateway->delete($where);
    }

    public function getTable(): string
    {
        return $this->gateway->getTable();
    }

    public function getGateway(): TableGatewayInterface
    {
        return $this->gateway;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->gateway->getAdapter();
    }

    public function getLastInsertId(): int|string
    {
        return $this->gateway->getLastInsertValue();
    }

    /**
     * Magic overload: Proxy calls to finder methods
     *
     * Examples of finder calls:
     * <code>
     * // METHOD                    // SAME AS
     * $repository->findByLabel('foo');    // $repository->findOneBy('label', 'foo');
     * $repository->findOneByLabel('foo'); // $repository->findOneBy('label', 'foo');
     * $repository->findAllByClass('foo'); // $repository->findAllBy('class', 'foo');
     * </code>
     *
     * @param  string $method             method name
     * @param  array  $arguments          method arguments
     * @return mixed
     * @throws Exception\BadMethodCallException  If method does not exist.
     */
    public function __call($method, $arguments)
    {
        ErrorHandler::start(E_WARNING);
        $result = preg_match('/(find(?:One|All)?By)(.+)/', $method, $match);
        $error  = ErrorHandler::stop();
        if (! $result) {
            throw Exception\BadMethodCallException::forCalledMethod(static::class, $method, $error);
        }
        return $this->{$match[1]}(lcfirst($match[2]), ...$arguments);
    }
}
