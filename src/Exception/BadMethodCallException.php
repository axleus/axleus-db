<?php

declare(strict_types=1);

namespace Axleus\Db\Exception;

use BadMethodCallException as BaseException;

use function sprintf;

final class BadMethodCallException extends BaseException
{
    public static function forCalledMethod(string $class, string $method, $error): self
    {
        return new self(
            sprintf(
                'Bad method call: Unknown method %s::%s',
                $class,
                $method
            ), 0, $error
        );
    }
}
