<?php

declare(strict_types=1);

namespace Axleus\Db\Feature\RelatedTable;

use Axleus\Db\RepositoryInterface;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Select;

use function class_exists;
use function in_array;
use function is_array;

abstract class AbstractTableReference implements ReferenceInterface
{
    final public const ALLOWED_REF_TYPES = [
        self::REF_DEPENDENT,
        self::REF_PARENT
    ];

    // [
    //     'dependent_table' => related FQCN | dependent Repository instance | null
    //     'parent_table' => related FQCN | parent Repository instance | null
    //     'column_map => ['local' => 'column', 'fk' => 'column']
    //     'columns'    => array of column names to return defaults to '*'
    // ]
    protected array $referenceMap = [];

    public function __construct()
    {
        if ($this->referenceMap !== []) {
           // $this->addReferenceMap($this->referenceMap);
        }
    }

    public function addReference(array $ref): self
    {
        if (! isset($ref['dependent_table']) && ! isset($ref['parent_table'])) {
            throw new Exception\InvalidReferenceException('Invalid reference detected.');
        }

        if (
            isset($ref['dependent_table'])
            && is_string($ref['dependent_table'])
            && ! class_exists($ref['dependent_table'], false)
        ) {
            throw new Exception\InvalidReferenceException('Invalid reference. Dependent class not found.');
        }

        if (
            isset($ref['parent_table'])
            && is_string($ref['parent_table'])
            && ! class_exists($ref['parent_table'], false)
        ) {
            throw new Exception\InvalidReferenceException('Invalid reference. Parent class not found.');
        }

        if (
            ! isset($ref['ref_type'])
            || ! in_array($ref['ref_type'], self::ALLOWED_REF_TYPES)
        ) {
            throw new Exception\InvalidReferenceException('Invalid ref_type detected.');
        }

        if (empty($ref['column_map'])) {
            throw new Exception\InvalidReferenceException('column_map must be an array.');
        }

        if (empty($ref['columns'])) {
            $ref['columns'] = Select::SQL_STAR;
        }

        $this->referenceMap[] = $ref;
        return $this;
    }

    public function addReferenceMap(array $map): self
    {
        foreach ($map as $ref) {
            if (is_array($ref) && $ref !== []) {
                $this->addReference($ref);
            }
        }
        return $this;
    }

    public function getReferenceMap(): array
    {
        return $this->referenceMap;
    }
}
