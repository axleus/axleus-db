<?php

declare(strict_types=1);

namespace Axleus\Db\Feature\RelatedTable;

trait RowGatewayTrait
{
    public function getDependentRows()
    {
        $fkValue = null;
        $method = 'findAllBy';
        $refProvider = $this->getReferenceProvider();
        $ref = $refProvider->getReferenceMap();
        $data = [];
        foreach ($ref as $map) {
            $columnMap = $map['column_map'];
            // check that column_map['local'] matches the primary key for this table
            if (in_array($columnMap['local'], $this->primaryKeyColumn)) {
                $fkValue = $this->primaryKeyData[$columnMap['local']];
            }
            $method .= \ucfirst($columnMap['fk']);
            $data[] = $map['dependent_table']->{$method}($fkValue);
        }
        $this->offsetSet('dependentRows', $data);
        return $data;
    }
}
