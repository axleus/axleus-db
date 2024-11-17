<?php

declare(strict_types=1);

namespace Axleus\Db\Feature;

use Db\Feature\ScrollablePdoResult\Result;
use Laminas\Db\Adapter\Driver\Pdo\Pdo as PdoAdapter;
use Laminas\Db\TableGateway\Feature\AbstractFeature;

use PDO;

final class ScrollablePdoResultFeature extends AbstractFeature
{
    public function preInitialize()
    {
        /** @var PdoAdapter */
        $driver = $this->tableGateway->getAdapter()->getDriver();
        if (! $driver instanceof PdoAdapter) {
            return;
        }
        $resultPrototype = new Result();
        $resultPrototype->setStatementMode(Result::STATEMENT_MODE_SCROLLABLE);
        $driver->registerResultPrototype($resultPrototype);
        /** @var PDO */
        $resource = $driver->getConnection()->getResource();
        $resource->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }
}
