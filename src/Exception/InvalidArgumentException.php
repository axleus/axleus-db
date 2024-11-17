<?php

declare(strict_types=1);

namespace Axleus\Db\Exception;

use Laminas\Db\Exception\InvalidArgumentException as BaseException;

final class InvalidArgumentException extends BaseException
{
    public static function invalidPrimaryKey(string $class, string $method, ?string $primaryKey): self
    {
        return new self(
            'Invalid Primary Key column identifier passed in: ' . $class . '::' . $method . ' received: ' . $primaryKey
        );
    }
}
