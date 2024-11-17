<?php

declare(strict_types=1);

namespace Axleus\Db\Feature\RelatedTable;

trait ReferenceProviderTrait
{
    protected ReferenceInterface $refProvider;

    public function setReferenceProvider(ReferenceInterface $refProvider): self
    {
        $this->refProvider = $refProvider;
        return $this;
    }

    public function getReferenceProvider(): AbstractTableReference
    {
        return $this->refProvider;
    }
}
