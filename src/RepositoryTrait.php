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
}
