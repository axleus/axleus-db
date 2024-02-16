<?php

declare(strict_types=1);

namespace Axleus\Db;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Where;
use Laminas\Stdlib\ErrorHandler;
use Axleus\Db\ModelTrait;
use InvalidArgumentException;

use const E_WARNING;

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
        ?string $whereColumn = null,
        ?array $joins = null,
        ?bool $returnArray = false
    ): EntityInterface|int {

        if ($entity instanceof EntityInterface) {
            $set = $this->hydrator->extract($entity);
        }
        if ($set === []) {
            throw new InvalidArgumentException('Repository can not save empty entity.');
        }
        try {
            if (! isset($set['id']) ) {
                // insert
                $this->gateway->insert($set);
                $set['id'] = $this->gateway->getLastInsertValue();
            } else {
                if ($whereColumn === null) {
                    throw new InvalidArgumentException('$whereColumn can not be null for updates');
                }
                $where = new Where();
                $this->gateway->update(
                    $set,
                    $where->equalTo($whereColumn, $set[$whereColumn]),
                    $joins ?? null
                );
            }
        } catch (\Throwable $th) {
            // will be caught by the commandbus
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

    public function delete(EntityInterface $entity): int { }

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
