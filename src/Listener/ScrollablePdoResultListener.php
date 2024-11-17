<?php

declare(strict_types=1);

namespace Axleus\Db\Listener;

use Db\Feature\ScrollablePdoResult\Result;
use Laminas\Db\Adapter\Driver\Pdo\Pdo as PdoAdapter;
use Laminas\Db\TableGateway\Feature\EventFeatureEventsInterface as TargetEvent;
use Laminas\Db\TableGateway\Feature\EventFeature\TableGatewayEvent;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

use PDO;

final class ScrollablePdoResultListener extends AbstractListenerAggregate
{

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            TargetEvent::EVENT_PRE_INITIALIZE,
            [$this, 'onPreInitialize'],
            $priority
        );
    }

    public function onPreInitialize(TableGatewayEvent $event)
    {
        $tableGateway = $event->getTarget();
        /** @var PdoAdapter */
        $driver = $tableGateway->getAdapter()->getDriver();
        if (! ($driver instanceof PdoAdapter)) {
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
